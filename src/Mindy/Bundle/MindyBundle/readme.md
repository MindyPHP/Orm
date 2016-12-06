# ResponseListener

```yaml
services:
    core.response_auto_listener:
        class: Mindy\Bundle\CoreBundle\EventListener\ResponseListener
        tags:
            - { name: kernel.event_listener, event: kernel.view, method: onKernelView }
```

# ExceltionListener

```yaml
services:
    mindy.bundle.mindy.example:
        class: Mindy\Bundle\MindyBundle\EventListener\ExceptionListener
        arguments: [ "@template", "@logger", "mindy/error/%s.html" ]
        tags:
            - { name: kernel.event_listener, event: kernel.exception, method: onKernelException }
```