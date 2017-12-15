---
layout: post
title:  "Overriding Service connection details using confit"
date:   2017-05-31 10:03:35 -0700
categories: Scribbles
---

It would be a bad idea to override your service connection details (service endpoints, certs, ciphers, transport type etc) using the `config/production.json`. Why?

Firstly, it will not work. 😃

Secondly, the source of truth for service connection details in `production` is `layout`. This design is for a good reason - the connection details are controlled by `TOPO`. The endpoint for a service connection can be altered by the TOPO management team and Command Center, based on traffic, security and data center maintenance requirements.

If we let developers control the connection data using `config/production.json` by overriding the default `layout`, the controls that the Command center and System admins use right now, to manage service connectivity, would not work at all.

### What about other configuration?

Configurations other than service connections (The `services` key in your config file) can be overridden by `config/production.json`. The `layout` file is applicable only to service connection details and controls only the `services` section of your configuration. Everything else works like just like any other `confit` setup and there are no other special restrictions like the service connection management.

### What about stage environment?

In stage environment, you can override the default `layout` values using `config/staging.json`. There is no special requirement on a stage/qa environment to restrict the overrides on service connectivity details.

### Is it OK to override transport type?

The `transport` property in the `layout.json` file of a service client module (example [here](https://github.paypal.com/NodeServices-R/node-onboardingapiserv/blob/master/layout.json#L4) ) is used to define the type of the client <-> service transport - `ppaas` , `asf` or `basic`.

It is not at all a good idea, to override this property using `confit` (Any of the `config/*.json` files). Why?

The `transport` property is not an environment aware property, meaning you don't expect the client to behave differently based on different environments. If the client uses a `ppaas` (Paypal rest clients) transport, you would expect the client to use the same transport on dev, stage and production etc.

If you want to convert a client to use a different transport - Let's say you want to convert a `ppaas` client to use `basic` transport to implement some custom request and response handling or serialization/deserialization requirements, then it would be a good idea to change the `layout` file itself. Overriding the `transport`, using `confit` will create troubles for you because, in production, this override will be ignored. The cleaner approach would be to fix the client module itself, by changing the `transport` value in the `layout.json` file.

### References

- [Confit](https://github.com/krakenjs/confit)

- [Servicecore](https://github.paypal.com/NodeInfra-R/node-servicecore/)
