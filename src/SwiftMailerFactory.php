<?php
namespace Virton;

use Psr\Container\ContainerInterface;

class SwiftMailerFactory
{
    /**
     * @param ContainerInterface $container
     * @return \Swift_Mailer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): \Swift_Mailer
    {
        if ($container->get('env') === 'production') {
            $transport = new \Swift_SendmailTransport();
        } else {
            $transport = new \Swift_SmtpTransport('localhost', 1025);
        }
        return new \Swift_Mailer($transport);
    }
}
