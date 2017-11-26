<?php declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\TwigBundle;

use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Sculpin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig_Environment;

/**
 * Template Resetter.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class TemplateResetter implements EventSubscriberInterface
{
    /**
     * Constructor.
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Sculpin::EVENT_BEFORE_RUN => 'beforeRun',
        ];
    }

    /**
     * Before run
     *
     * @param SourceSetEvent $sourceSetEvent Source Set Event
     */
    public function beforeRun(SourceSetEvent $sourceSetEvent)
    {
        if ($sourceSetEvent->updatedSources()) {
            $this->twig->clearTemplateCache();
        }
    }
}
