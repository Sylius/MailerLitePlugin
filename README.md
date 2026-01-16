<p align="center">
    <a href="https://sylius.com" target="_blank">
        <picture>
          <source media="(prefers-color-scheme: dark)" srcset="https://media.sylius.com/sylius-logo-800-dark.png">
          <source media="(prefers-color-scheme: light)" srcset="https://media.sylius.com/sylius-logo-800.png">
          <img alt="Sylius Logo." src="https://media.sylius.com/sylius-logo-800.png">
        </picture>
    </a>
</p>

<h1 align="center">MailerLite Plugin</h1>

<p align="center"><a href="https://sylius.com/plugins/" target="_blank"><img src="https://sylius.com/assets/badge-official-sylius-plugin.png" width="200"></a></p>

<p align="center">This plugin integrates MailerLite with Sylius for newsletter and email marketing.</p>

## Features

- Sync customers to MailerLite subscriber lists
- Subscribe users during registration (with consent)
- Manage newsletter subscriptions from customer account

## Installation

1. Require plugin with composer:

    ```bash
    composer require sylius/mailer-lite-plugin
    ```

2. Import configuration:

    ```yaml
    # config/packages/sylius_mailerlite.yaml
    imports:
        - { resource: "@SyliusMailerLitePlugin/config/config.yaml" }
    ```

3. Import routes:

    ```yaml
    # config/routes/sylius_mailerlite.yaml
    sylius_mailerlite:
        resource: "@SyliusMailerLitePlugin/config/routes.yaml"
    ```

4. Configure your MailerLite API key:

    ```yaml
    # config/packages/sylius_mailerlite.yaml
    sylius_mailerlite:
        api_key: '%env(MAILERLITE_API_KEY)%'
    ```

5. Clear cache:

    ```bash
    bin/console cache:clear
    ```

## Configuration

Add your MailerLite API key to `.env`:

```
MAILERLITE_API_KEY=your_api_key_here
```

## Security issues

If you think that you have found a security issue, please do not use the issue tracker and do not post it publicly.
Instead, all security issues must be sent to `security@sylius.com`.

## Community

For online communication, we invite you to chat with us and other users on [Sylius Slack](https://sylius.com/slack).

## License

This plugin is released under the [MIT License](LICENSE).
