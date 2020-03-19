<?php

namespace contactformextended;

use Craft;
use contactformextended\validators\GumpValidator;
use craft\base\Plugin;
use craft\contactform\Mailer;
use craft\contactform\events\SendEvent;
use craft\contactform\models\Submission;
use craft\helpers\StringHelper;
use yii\base\Event;
use yii\base\ModelEvent;
use yii\web\NotFoundHttpException;

class ContactFormExtended extends Plugin
{
    public static $plugin;

    public $schemaVersion = '1.0.1';

    public $hasCpSettings = true;

    protected $validator = null;

    // testing versioning again

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        Event::on(Submission::class, Submission::EVENT_BEFORE_VALIDATE, function(ModelEvent $event)
        {
            $event = $this->getValidator()->check($event);
        });

        Event::on(Mailer::class, Mailer::EVENT_BEFORE_SEND, function(SendEvent $event)
        {
            $siteHandle = Craft::$app->getSites()->getCurrentSite()->handle;

            $toEmails = ContactFormExtended::$plugin->getSettings()->toEmail[$siteHandle] ?? [];

            if ( $toEmails )
            {
                $event->toEmails = is_string($toEmails) ? StringHelper::split($toEmails) : $toEmails;
            }

            $view = Craft::$app->getView();
            $oldTemplatesPath = $view->getTemplatesPath();

            $view->setTemplatesPath(self::getInstance()->getBasePath() . '/templates');

            $variables = [];

            $variables['fromName']  = $event->submission->fromName;
            $variables['fromEmail'] = $event->submission->fromEmail;
            $variables['subject']   = $event->submission->subject;

            if ( is_array($event->submission->message) )
            {
                foreach ( $event->submission->message as $field => $value )
                {
                    $variables[$field] = $value;
                }
            }
            else
            {
                $variables['message'] = $event->submission->message;
            }

            $html = $view->renderTemplate('email', $variables);

            $event->message->setHtmlBody($html);

            $view->setTemplatesPath($oldTemplatesPath);
        });
    }

    protected function getValidator()
    {
        if ( $this->validator !== null )
        {
            return $this->validator;
        }
        
        return (new GumpValidator((new \GUMP), $this->getSettings()));
    }

    protected function createSettingsModel()
    {
        return new \contactformextended\models\Settings();
    }

    protected function settingsHtml()
    {
        return \Craft::$app->getView()->renderTemplate('contact-form-extended/settings', [
            'settings' => $this->getSettings()
        ]);
    }
}
