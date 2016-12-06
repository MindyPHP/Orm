<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 13/10/16
 * Time: 09:56
 */

namespace Mindy\Bundle\MindyBundle\Helper;

use Mindy\Bundle\TemplateBundle\TemplateFinder\TemplateFinderInterface;
use Mindy\Template\Renderer;

class Mail
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var string
     */
    protected $defaultFrom;
    /**
     * @var TemplateFinderInterface
     */
    protected $finder;
    /**
     * @var Renderer
     */
    protected $template;

    /**
     * Mail constructor.
     * @param \Swift_Mailer $mailer
     * @param TemplateFinderInterface $finder
     * @param Renderer $template
     * @param $defaultFrom
     */
    public function __construct(\Swift_Mailer $mailer, TemplateFinderInterface $finder, Renderer $template, $defaultFrom)
    {
        $this->mailer = $mailer;
        $this->finder = $finder;
        $this->template = $template;
        $this->defaultFrom = $defaultFrom;
    }

    /**
     * @param $subject
     * @param $to
     * @param $template
     * @param array $data
     * @param array $attachments
     * @return \Swift_Message
     * @throws \Exception
     */
    protected function getMessage($subject, $to, $template, array $data = [], array $attachments = [])
    {
        $message = \Swift_Message::newInstance();

        $message->setSubject($subject);
        $message->setFrom($this->defaultFrom);
        $message->setTo($to);

        if ($this->finder->find($template . '.html')) {
            $message->setBody($this->template->render($template . '.html', $data), 'text/html');

            if ($this->finder->find($template . '.txt')) {
                $message->addPart($this->template->render($template . '.txt', $data), 'text/plain');
            }
        } else {
            throw new \Exception('Unknown template: ' . $template . '.html');
        }

        foreach ($attachments as $fileName => $options) {
            $attachment = \Swift_Attachment::fromPath($fileName);
            if (!empty($options['fileName'])) {
                $attachment->setFilename($options['fileName']);
            }
            if (!empty($options['contentType'])) {
                $attachment->setContentType($options['contentType']);
            }
            $message->attach($attachment);
        }

        return $message;
    }

    /**
     * @param $subject
     * @param $to
     * @param $template
     * @param array $data
     * @param array $attachments
     * @return int
     * @throws \Exception
     */
    public function send($subject, $to, $template, array $data = [], array $attachments = [])
    {
        $message = $this->getMessage($subject, $to, $template, $data, $attachments);
        return $this->mailer->send($message);
    }
}