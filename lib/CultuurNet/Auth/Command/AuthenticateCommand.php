<?php

namespace CultuurNet\Auth\Command;

use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

use \Guzzle\Http\Client;
use \Guzzle\Http\Url;

use \Guzzle\Plugin\Cookie\CookiePlugin;
use \Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;

use \CultuurNet\Auth\Session\JsonSessionFile;
use Symfony\Component\Console\Question\Question;

class AuthenticateCommand extends Command
{
    /**
     * @var AuthServiceFactory
     */
    protected $authenticateServiceFactory;

    /**
     * @inheritdoc
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->authenticateServiceFactory = new AuthServiceFactory();
    }

    protected function configure()
    {
        $this
          ->setName('authenticate')
          ->setDescription('Perform OAuth authentication')
          ->addOption(
            'base-url',
            NULL,
            InputOption::VALUE_REQUIRED,
            'Base URL of the UiTiD service provider to authenticate with'
          )
          ->addOption(
            'username',
            'u',
            InputOption::VALUE_REQUIRED,
            'User name to authenticate with'
          )
          ->addOption(
            'password',
            'p',
            InputOption::VALUE_REQUIRED,
            'Password to authenticate with'
          )
          ->addOption(
            'callback',
            NULL,
            InputOption::VALUE_REQUIRED,
            'OAuth callback, for demonstrational purposes',
            'http://example.com'
          )
          ->addOption(
            'debug',
            NULL,
            InputOption::VALUE_NONE,
            'Output full HTTP traffic for debugging purposes'
          );
    }

    protected function execute(InputInterface $in, OutputInterface $out)
    {
        parent::execute($in, $out);

        $consumer = $this->session->getConsumerCredentials();

        $authBaseUrl = $this->resolveBaseUrl('auth', $in);

        $authService = $this->authenticateServiceFactory->createService(
          $in,
          $out,
          $authBaseUrl,
          $consumer
        );

        $callback = $in->getOption('callback');

        $temporaryCredentials = $authService->getRequestToken($callback);

        $client = new Client($authBaseUrl, array('redirect.disable' => true));

        // @todo check if logging in on UiTiD requires cookies?
        $cookiePlugin = new CookiePlugin(new ArrayCookieJar());
        $client->addSubscriber($cookiePlugin);

        $user = $in->getOption('username');
        $password = $in->getOption('password');

        //$dialog = $this->getHelperSet()->get('dialog');
        $dialog = $this->getHelper('question');
        /* @var \Symfony\Component\Console\Helper\QuestionHelper $dialog */

        while (NULL === $user) {
            $userNameQuestion = new Question('User name: ');
            $user = $dialog->ask($in, $out, $userNameQuestion);
            //$user = $dialog->ask($out, 'User name: ');
        }

        while (NULL === $password) {
            $passwordQuestion = new Question('Password: ');
            $passwordQuestion->setHidden(true);
            $passwordQuestion->setHiddenFallback(false);
            $password = $dialog->ask($in, $out, $passwordQuestion);
        }

        $postData = array(
          'email' => $user,
          'password' => $password,
          'submit' => 'Aanmelden',
          'token' => $temporaryCredentials->getToken(),
        );

        $response = $client->post('auth/login', NULL, $postData)->send();

        // @todo check what happens if the app is already authorized

        $postData = array(
          'allow' => 'true',
          'token' => $temporaryCredentials->getToken(),
        );

        $response = $client->post('auth/authorize', NULL, $postData)->send();

        $location = $response->getHeader('Location', true);

        $url = Url::factory($location);

        $oAuthVerifier = $url->getQuery()->get('oauth_verifier');

        $user = $authService->getAccessToken($temporaryCredentials, $oAuthVerifier);
        $this->session->setUser($user);

        $out->writeln('user id: ' . $user->getId());
        $out->writeln('access token: ' . $user->getTokenCredentials()->getToken());
        $out->writeln('access token secret: ' . $user->getTokenCredentials()->getSecret());

        $sessionFile = $in->getOption('session');
        if (NULL !== $sessionFile) {
            JsonSessionFile::write($this->session, $sessionFile);
        }
    }
}
