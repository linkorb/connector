<!-- Managed by https://github.com/linkorb/repo-ansible. Manual changes will be overwritten. -->
connector
============

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

Build status: [![Release](https://github.com/linkorb/connector/actions/workflows/30-release-and-build.yaml/badge.svg)](https://github.com/linkorb/connector/actions/workflows/30-release-and-build.yaml)




## Usage

Please refer to `examples/` for usage examples

## Contributing

We welcome contributions to make this repository even better. Whether it's fixing a bug, adding a feature, or improving documentation, your help is highly appreciated. To get started, fork this repository then clone your fork.

Be sure to familiarize yourself with LinkORB's [Contribution Guidelines](/CONTRIBUTING.md) for our standards around commits, branches, and pull requests, as well as our [code of conduct](/CODE_OF_CONDUCT.md) before submitting any changes.

If you are unable to implement changes you like yourself, don't hesitate to open a new issue report so that we or others may take care of it.
## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).
By the way, we're hiring!
