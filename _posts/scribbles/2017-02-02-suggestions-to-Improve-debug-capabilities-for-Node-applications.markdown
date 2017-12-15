---
layout: post
title:  "Suggestions to Improve debug capabilities for Node.js applications"
date:   2017-02-02 10:23:41 -0700
categories: Scribbles
---

### Altus UI to restart an app in debug mode

We need a capability to restart any particular machine with `debug` mode enabled, without any code change.

Altus team should provide a UI, that accepts say comma separated module names (for which we need to enable debug mode) and then set `export NODE_DEBUG=blah,blahh` and restart the application.

Any current manifest could be moved to `debug` mode without code change and re-build.

Also we should maintain the list of common modules for the debug option like `express:`, `http`, `net` etc.

### Debuglogs in Infra Modules (or add dbrickashaw support)

All the `NodeInfra` modules should integarte with `debuglog` and should log enough details for all the possible error scenarios.

We should share a list/registry of debug options that be set for the `NODE_DEBUG` so that developers need not browse through individual components.For example `servicecore:`, `paypalize:` etc.

  OR

We should add `dbrickashaw` to all the infra modules and expose and end point to publish the debug logs.

### Log more request details to application log

Log more request details to application log. Right now we use the `combined` format of `express/morgan` to log request data.

It would be helpful to log more details like `correlationId`, `pp_remote_addr` etc by using the `morgan.token` API.

These additional details in application logs, are really helpful, in the case any failure happened that prevented CAL logs all together.

### Log the 500 errors to application log

The `uncaught` errors originated by `async` errors would be captured by the `shutdown` middleware and that error gets logged to application log also.

However `sync` errors, caught by `500` error handler may or may not gets logged to application logs.

Some application add `500` `error handlers` that log errors to CAL as well as console (application log).

It would be great if we can add a error handler middleware in `paypalize` only to log to console (application log).
