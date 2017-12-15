---
layout: post
title:  "NGINX configuration of a PayPal Web application"
date:   2017-02-07 18:06:25 -0700
categories: Node.js Infra
---

At PayPal, the runtime architecture is `RHEL` and all the libraries are distributed as `rpm` formats.
We are building an rpm distribution for the [nginx from the source](http://nginx.org/en/docs/configure.html), because of the following reasons.

- We needed fine-tuned control over the NGINX components and wanted extra levels of customization to add additional modules.
  We would want to enable some modules like `http_ssl_module`, `http_gzip_static_module`, etc and disable some of the modules like `mail_smtp_module`.
- We wanted to add `naxsi-core` module - an open source, high performance, low rules maintenance, Web Application Firewall module for NGINX.
- We wanted to add modules like `ngx_http_substitutions_filter_module` and `ngx_headers_more` to handle use cases like Filtering response body, Adding/Removing request headers etc.

Details of the RPM source repository of NGINX and details on how to build a new version are linked below.

[PayPal SPRM Repo for NGINX](https://github.paypal.com/PayPalStandardBase/ppsb_srpms/tree/master/nginx)

[NGINX src build configure](https://github.paypal.com/PayPalStandardBase/ppsb_srpms/tree/master/nginx#basic-configuration)

[NGINX Additional modules](https://github.paypal.com/PayPalStandardBase/ppsb_srpms/tree/master/nginx#additional-modules)

[Process to upgrade to a latest version](https://github.paypal.com/PayPalStandardBase/ppsb_srpms/tree/master/nginx#how-to-upgrade-to-latest-version)


There are three levels of NGINX configuration supported at PayPal.

1. Base conf. This is Conf part of the rpm and can be found [here](https://github.paypal.com/PayPalStandardBase/ppsb_srpms/blob/master/nginx/nginx.conf).
2. Web app level conf. This is the generic conf for all the PayPal web applications (managed by infra team) - [check it out](https://github.paypal.com/NodeOps/nginx-conf/blob/master/www.paypal.com.conf)
3. Application specific conf. This is an override conf added by applications to handle special use cases. [Template for an override](https://github.paypal.com/NodeOps/gpaas-paypal/blob/master/examples/nginx.conf.override)

## Base conf (part of the NGINX RPM)

Location  : `/etc/nginx/nginx.conf`

When we build the NGINX rpm distribution, we add a generic `conf` to the rpm itself so that NGINX can be used by any team here at PayPal.
The `conf` file gets added to the location `/etc/nginx/nginx.conf` by default.
The configuration is pretty much generic such that, it has no web app or route specific configurations. The `server` section is not added to the conf, and it is up to the user to define `server` and route specific conf (`location`) in `/etc/nginx/conf.d/` directory.

[Current version](https://github.paypal.com/PayPalStandardBase/ppsb_srpms/blob/master/nginx/nginx.conf)

Following are some of the base conf definitions:
  - define `log format`
  - `events` section
  - `header`, `body` and `timeout` default configs
  - `compression`(`gzip`) configs

## Web app level conf (Common for all the PayPal apps)

Location  : `/etc/nginx/conf.d/www.paypal.conf`

Web app specific (`server` and `location`) configurations are added by the `www.paypal.conf` conf file.
This is the common conf for all the web applications at PayPal and this conf is managed by the Node.js Infra team.

[Current Version](https://github.paypal.com/NodeOps/nginx-conf/blob/master/www.paypal.com.conf)

Following are some of the web app conf definitions:
  - `upstream` proxy definition. For node.js applications, this is the node app server details (host and port)
  - `server` definition. By default the server listens to only `https` traffic at port `443`
  - `ssl` definitions for the server
  - `header` and `body` configs for the server
  - `location` definitions. All the route definitions. This include
    - Application route (including the `co-brand` routes)
    - `ecv` route for the load balancer
    - `meta` route for tools
    - `fallback` routes for `errorsnodeweb`

### How to distribute the web app level conf to machines?

There are two major challenges in standardizing a web app NGINX config (used by all the applications),

1. How to distribute a change in the config to all the machines running at the PayPal front-tier stack (web apps)? There could be production issues that, we may need to address as soon as possible and there should be a clean solution to propagate urgent conf change to all the applications.
2. There are couple of application specific data - namely `app name` and `app base uri` - that need to be configured, in the `conf` file. We need a solution to define variables for these app specific data in the `conf` and then later substitute the actual values at the application deploy time.

To distribute critical and urgent nginx conf changes to production machines, we designed the web app level nginx as a `global dependency package` (A package added by the `assembler` as part of the CI build).

At the build time, the `assembler` tool bundles the nginx conf with the application manifest. At the deploy time, the LCM scripts copies over the nginx conf to the target location `/etc/nginx/conf.d/www.paypal.conf`. This is a [force copy](https://github.paypal.com/NodeOps/gpaas-paypal/blob/master/deploy/util.sh#L679-L694), to replace the old file.

To [release](https://github.paypal.com/NodeOps/nginx-conf#how-to-rollout-a-new-version-of-nginx-conf-global-dependency) any changes in the `www.paypal.conf` configuration, infra team pushes a new version of the `nginx_conf` global dependency package. Any new CI build picks up this change and at the deploy time LCM script replaces the existing conf with the new file.

[How to rollout a new version](https://github.paypal.com/NodeOps/nginx-conf#how-to-rollout-a-new-version-of-nginx-conf-global-dependency)

#### Variable substitutions

`Application name` and `application base uri` are two variables in the nginx conf that need to be substituted based on the application details. The `www.paypal.com` in the [global dependency pkg](https://github.paypal.com/NodeOps/nginx-conf/blob/master/www.paypal.com.conf) defines variables like `$APPNAME` and `$APP_REQUEST_URI`. These variables are substituted at deploy time using `sed` at the LCM `activate` [step](https://github.paypal.com/NodeOps/gpaas-paypal/blob/master/deploy/util.sh#L64-L88).


## Application level nginx conf override

Location : `<app_base_dir>/nginx.conf`

Template file: `<app_base_dir>/nginx.conf.override`

If the application team want to configure any special routing rule or want to override the default configurations (the first two levels), they can add an application level override nginx conf file.

[How to override the nginx.conf for your application?](https://github.paypal.com/NodeOps/gpaas-paypal#how-to-override-the-nginxconf-for-your-application)

This conf file is a complete override, such that `/etc/nginx/nginx.conf` is not at all used when an override is detected.

A [starting template](https://github.paypal.com/NodeOps/gpaas-paypal/blob/master/examples/nginx.conf.override) could be used by the application teams to define the configuration. This template uses Variables like `APP_REQUEST_URI`, `APPNAME`, `NGINX_LOG_DIR`, `NGINX_CONF_DIR` etc, so that the configuration would work independent of the deployment environment.

These variables are substituted at deploy time using `sed` at the LCM scripts [here](https://github.paypal.com/NodeOps/gpaas-paypal/blob/master/deploy/util.sh#L732-L886).
