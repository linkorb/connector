Connector: Database connection resolver
=======================================

Connector helps you to manage your app's database connection configurations
in dynamic environments.

Your app simply requests a configuration from connector by a name.

Connector will resolve the name into a full database configuration object
with properties like username, password, address, port, protocol etc.

Connector then helps you to turn this Config object into a PDO connection.

Next to the common Config properties, Connector also allows you to define
custom properties on a a database config instance. These custom properties
can then be used by your app to configure the application behaviour.

## Cascading configuration

A configuration may define connection properties directly,
or refer to a `server` and/or `cluster` by name.

This enables cascading configuration at 3 levels:

* db
* server
* cluster

Using this feature you can define the server or cluster at the db level,
and configure address, username, password, port and custom properties
at a higher level. This way you can quickly mass-reconfigure all
dbs on a given server or cluster.

## Usage

Please refer to `examples/` for usage examples

## License

MIT (see [LICENSE.md](LICENSE.md))

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!
