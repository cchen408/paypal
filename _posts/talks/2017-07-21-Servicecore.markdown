---
layout: post
title:  "A deep-dive into Servicecore"
date:   2017-07-21 10:23:41 -0700
categories: Tech Talks
---

## What is servicecore?

[servicecore](https://github.paypal.com/NodeInfra-R/servicecore) is the NodeInfra  service invocation library for creating and configuring service clients (AKA service wrappers) to interact with PayPal services.

`Servicecore` clearly separates the `client`, the `configuration`, the `connection data` and the `connection resolver` into independent but inter-related components.

### Client

`Servicecore` is a factory for creating clients. You need to provide the `client module` and the `configuration` as an input for this Factory function.

`Servicecore.create()` lets you create a client, that you can use to invoke a service.

[How to create a client instance?](https://github.paypal.com/NodeInfra-R/servicecore#creating-client-instances)

The client (created by the servicecore factory) has all the capabilities (http agent, timeout configurations, circuit breaker integration etc) to perform a downstream service call.

#### What is a service `client module` (service wrapper module)?

Developers create a client module and add that to the [NodeServices-R](https://github.paypal.com/NodeServices-R/) organization. This client module has important details around the type of transport (`ppaas`, `asf` or `basic`), connection details (service name), scopes etc.

[Example of a `ppaas` client module](https://github.paypal.com/NodeServices-R/node-fimanagementservice_ca)

[Example of an `asf` client module](https://github.paypal.com/NodeServices-R/node-paymentlookup)

[Example of a `basic` client module](https://github.paypal.com/NodeServices-R/node-walletcontactserv)

### Configurations

Configurations are controlled by environment aware [confit](https://github.com/krakenjs/confit) module.

[Servicecore Configuration](https://github.paypal.com/NodeInfra-R/servicecore#configuration)

### Connection data

The `layout.json` file act as the base for the connection data - `hostname` and `port` combination.

[layout.json example](https://github.paypal.com/NodeServices-R/node-manualvettingserv/blob/master/layout.json)

### Connection resolver

[topos module](https://github.paypal.com/NodeInfra-R/node-topos)

## What is a transport?

- [servicecore transport](https://github.paypal.com/NodeInfra-R/servicecore#transports)

- [Registering a new Transport](https://github.paypal.com/NodeInfra-R/servicecore#registering-transports)

## Servicecore Features


- [servicecore readme](https://github.paypal.com/NodeInfra-R/servicecore)

- [servicecore wiki](https://github.paypal.com/NodeInfra-R/servicecore/wiki)

- [Service Invocation Details](https://github.paypal.com/suchothendav/node-architecture/blob/master/servicecore/TCPCommunications.md)

- [servicecore-profiler](https://github.paypal.com/NodeInfra-R/servicecore-profiler) - CAL logging and profiling.
- [servicecore-securitycontext](https://github.paypal.com/NodeInfra-R/servicecore-securitycontext) - PPaaS service security.
- [servicecore-hysterix](https://github.paypal.com/NodeInfra-R/servicecore-hystrix) - Circuit breaker.

### Servicecore Plugins

- [Writing Plugins](https://github.paypal.com/NodeInfra-R/servicecore#writing-plugins)

- [Adding Plugins](https://github.paypal.com/NodeInfra-R/servicecore#adding-plugins)

### Circuit Breaker

- [Hystrix](http://doc.akka.io/docs/akka/snapshot/common/circuitbreaker.html)

- [Levee](https://github.com/krakenjs/levee)

- https://github.paypal.com/NodeInfra-R/servicecore-hystrix

- [Circuit Breakers for transport calls](https://github.paypal.com/NodeInfra-R/servicecore#circuit-breakers-for-transport-calls)


## Servicecore 2.0 Upgrade

[CHANGELOG](https://github.paypal.com/NodeInfra-R/servicecore/blob/2.x/CHANGELOG.md#200)

[Upgrade guide](https://github.paypal.com/NodeInfra-R/servicecore/blob/2.x/UPGRADE.md)

## What is Next?

- `Servicecore 3`

- [Persistent Connections](https://github.paypal.com/suchothendav/node-architecture/blob/master/servicecore/PersistentConnections.md)

- [Clientmodule-less servicecore](https://github.paypal.com/suchothendav/node-architecture/blob/master/servicecore/Clientmodule-less.md)

- [Retry policy](https://github.paypal.com/suchothendav/node-architecture/blob/master/servicecore/RetryPolicy.md)
