---
layout: post
title:  "Servicecore dynamic path memory leak fix"
date:   2017-10-20 10:23:41 -0700
categories: Scribbles
---

**Have you noticed any memory leak issues with your application, recently?**

**Do you happen to invoke a downstream service with dynamic values (like an account number, id or username) in the path of the service?**

**If yes, continue reading this.**

Thanks to `Wilburn, Greg <gwilburn@paypal.com>` and `Chellachamy, Vijayaraj <vchellasamy@paypal.com>`, we have uncovered a potential scenario for memory leak when using dynamic values in a `path` option of a service call.

*Greg Wilburn* is in the process of authoring a blog with detailed account of the steps that lead to the discovery of the memory leak, the process of memory leak analysis in general, the tools used in the investigation (like `N|Solid`, `Chrome dev tools` etc). In the mean time, this document will serve the purpose of communicating the details around the steps you can take to fix the issue.

### How do i check if my app is using dynamic values as path option in a service call?

Check for the `options` object passed over to servicecore `request` API. This would be part of the client module code, if you are using fat wrapper clients (like `basic` transport). For thin `ppaas` clients, check the `request` function call.

The `path` option of `servicecore` lets you define the API path to operate on a resource on the server. Following is an examples of dynamic values in the `path` option.

```js
    activities.request(req, {
        method: 'GET',
        path: '/v1/activities/123456', // activity id 123456 is Dynamic value that changes for every request.
        qs: new ActivitySearchModel()
    }, function (error, response) {
        callback(error, response && response.body);
    });
```

### What is the root cause of the memory leak?

The [Hystrix implementation](https://github.paypal.com/NodeInfra-R/servicecore-hystrix) of servicecore is a critical feature that enables [`circuit breaker`](http://doc.akka.io/docs/akka/snapshot/common/circuitbreaker.html) capability for downstream service calls. Servicecore uses the `path` option as the key to store `Circuit Breaker` objects of a service call. Circuit breaker object keeps track of, number of failures (among others) on a particular downstream service call. The unique value for a particular endpoint would be the combination of service name and the path of the service (service endpoint).

If the `path` option has dynamic values (like account number, id, username etc), the resolved path value would be different for every downstream service call. Because of this, servicecore inadvertently creates multiple objects (keys are different for every service call, as opposed to using a unique value for a service endpoint) of circuit breaker objects, and the memory grows with every service call.

Servicecore should have been using a static key (or make sure there are no dynamic values in the resolved path) to store the circuit breaker object that keeps track of failures per service endpoint.

## What is the fix for this issue?

We need to make sure that the key used to store the circuit breaker object would be a static and unique string (per service endpoint).

There are two options to fix this issue.

**Option 1** : Use an additional static `pathPattern` option to define the unique pattern of the service `path`.

```js

    client.request({
        pathPattern: "/customer/{customer_id}/account/invoice/{invoice_id}" //OR "/customer/:customer_id/account/invoice/:invoice_id"
        path: "/customer/12345/account/invoice/45637dff67"
        .
        .
        .
    })
```

`Hystrix` module will use the `pathPattern` option (if it exists) as the key to store circuit breaker Objects.

**Option 2** : Do not construct the resolved `path`. Instead use the pattern as the `path` option and pass along the key-value par object as `pathparams` option.

```js

    client.request({
        path: "/customer/{customer_id}/account/invoice/{invoice_id}"
        pathparams: {
            customer_id: 12345,
            invoice_id: '45637dff67'
        },
        .
        .
        .
    })
```

here the `path` option would be static (and unique for the path), so there won't be any issues using this value as the key to store the Circuit breaker objects.
