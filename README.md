# Dyn 53

Use amazon Route 53 as a Dyn DNS provider

Run the command manually or schedule it in a Cron, the choice is up to you

    dyn53.phar update --domain="home.example.com" --ip="127.0.0.1"

With this tool you control your records, you can manage any domain from anywhere. (as long as you have access to do so)

If the record you are trying to update doesn't exist, it will create it as an A record with a TTL of 60 seconds.

This service only supports IPV4, support for IPV6 is not planned for the moment.

## Commands

All the options below can be specified in a configuration file in the ini format

    configuration.ini

    key = "my_key"
    secret = "my_secret"


__Options for all commands__

- `config`: absolute path to a configuration file (`.ini` format, optional)
- `key`: your Amazon Web Services key
- `secret`: your Amazon Web Services secret

### `update`

Updates the domain if the detected IP address changed

__Options:__

- `zone`: the Amazone Route 53 zone ID
- `domain`: the domain to update
- `ip`: An ip to set (optional)
- `policy`: How to detect the IP address (uses http://myexternalip.com/ by default)

__Examples:__

    //forced IP
    dyn53.phar update --key="my_key" --secret="my_secret" --zone="/hostedzone/Z3EW3A21XNEPC8" --domain="home.example.com" --ip="127.0.0.1"

    //automatic IP detection
    dyn53.phar update --key="my_key" --secret="my_secret" --zone="/hostedzone/Z3EW3A21XNEPC8" --domain="home.example.com"

    //configuration file

    -> /etc/dyn53.ini
    key = "my_key"
    secret = "my_secret"
    zone = "/hostedzone/Z3EW3A21XNEPC8"

    dyn53.phar update --config="/etc/dyn53.ini" --domain="home.example.com"


### `list`

List all the available zones in your Amazon Web services account

    dyn53.phar list --config="/etc/dyn53.ini"

    +--------------+----------------------------+
    | Name         | ID                         |
    +--------------+----------------------------+
    | onigoetz.ch. | /hostedzone/Z3EW3A21XZEPC8 |
    +--------------+----------------------------+

## Contributing

if you want to add a functionality or discover a bug; fill an issue or create a pull request. I would be happy to help.
