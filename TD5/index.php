<?php
$loader = include('vendor/autoload.php');
$loader->add('', 'src');

$app = new Silex\Application();

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__ . '/views'));

$app['model'] = new Sondages\Model(
        '127.0.0.1', // hote
        'sondage', // bdd
        'root', // User
        ''     // mdp
);

$app->get('/', function() use ($app) {
    return $app['twig']->render('home.html.twig', array(
                'user' => $app['model']->checkConnection($app['session']->get('user'), $app)
    ));
})->bind('home');

$app->match('/logout', function() use ($app) {
    $app['session']->clear();
    return $app->redirect('/');
})->bind('logout');

$app->match('/register', function() use ($app) {
    if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') == 'POST') {
        if ((null !== filter_input(INPUT_POST, 'login')) && (null !== filter_input(INPUT_POST, 'password'))) {
            $boolRegister = $app['model']->register(filter_input(INPUT_POST, 'login'), filter_input(INPUT_POST, 'password'));
            return $app['twig']->render('register.html.twig', array(
                        'register' => $boolRegister)
            );
        }
    }
    return $app['twig']->render('register.html.twig');
})->bind('register');


$app->match('/login', function() use ($app) {
    if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
        if (null!= filter_input(INPUT_POST, 'login') && null!= filter_input(INPUT_POST, 'password')) {
            $app['model']->login(filter_input(INPUT_POST, 'login'), filter_input(INPUT_POST, 'password'), $app);
        }
    }
    return $app['twig']->render('login.html.twig', array(
                'user' => $app['model']->checkConnection($app['session']->get('user'))
    ));
})->bind('login');


$app->match('/polls', function() use ($app) {
    return $app['twig']->render('polls.html.twig', array(
                'polls' => $app['model']->getPolls(),
                'user' => $app['model']->checkConnection($app['session']->get('user'))
    ));
})->bind('polls');

$app->match('/pollsId/{id}', function($id) use ($app) {
    if (null!= filter_input(INPUT_POST, 'answer')) {
        if (!$app['model']->answered($app['session']->get('user'), $id)) {
            $app['model']->addAnswer($app['session']->get('user'), $id, filter_input(INPUT_POST, 'answer'));
        }
    }

    return $app['twig']->render('pollsId.html.twig', array(
                'userAnswered' => $app['model']->answered($app['session']->get('user'), $id),
                'polls' => $app['model']->getPollsFromId($id),
                'answers' => $app['model']->getTotal($id)['answers'],
                'total' => $app['model']->getTotal($id)['total']
    ));
})->bind('pollsId');

$app->match('/myPolls', function() use ($app) {


    return $app['twig']->render('myPolls.html.twig', array(
                'user' => $app['model']->checkConnection($app['session']->get('user')),
                'sondages' => $app['model']->getMyPolls($app['session']->get('user'))
    ));
})->bind('myPolls');


$app->match('/create', function() use ($app) {
    if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') == 'POST') {
        if (null!= filter_input(INPUT_POST, 'question') &&
                null!= filter_input(INPUT_POST, 'answer1') &&
                null!= filter_input(INPUT_POST, 'answer2')) {

            $question = filter_input(INPUT_POST, 'question');
            $answer1 = filter_input(INPUT_POST, 'answer1');
            $answer2 = filter_input(INPUT_POST, 'answer2');
            $answer3 = filter_input(INPUT_POST, 'answer3');
            return $app['twig']->render('create.html.twig', array(
                        'create' => $app['model']->addPoll($question, $answer1, $answer2, $answer3)));
        }
    }
    return $app['twig']->render('create.html.twig', array(
                'user' => $app['model']->checkConnection($app['session']->get('user'))
    ));
})->bind('create');

// remonte les erreurs
$app->error(function($error) {
    throw $error;
});

//$app->boot();
$app->run();
