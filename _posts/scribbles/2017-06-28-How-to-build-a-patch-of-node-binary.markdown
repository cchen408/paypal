---
layout: post
title:  "How to build a patch of node.js binary"
date:   2017-06-28 12:30:33 -0700
categories: Scribbles
---

Even wondered, how to patch a node.js version with your own changes (or changes that are yet to be released)? Well, here are the details on how to do it.

Why would we want to build a patch?

- If there is a known security vulnerability and the fix is yet to be released as a patch version (by the node core folks), and PayPal Node Infra would want to avail that fix within PayPal, at the earliest.

- If there is a critical bug that we need to fix as soon as possible, before waiting for an official release of node.js patch version.

- If there is an important feature that is approved (and merged) and we would not want to wait for the official LTS release of the feature. By building an internal patch we can test the feature, find early issues (if any), give feedback, measure performance etc.

- If PayPal Node Infra would want to make experimental changes on the core version before sending an official PR. This helps us verify the changes and collect important metrics.

## Steps to build the Nodejs Binary patch

### To pull the image

```
docker pull dockerhub.paypalcorp.com/suchothendav/nodebinarybuilder:0.0.1

```

### To build the binary in a container

1)  Run the container

```
docker run -it dockerhub.paypalcorp.com/suchothendav/nodebinarybuilder:0.0.1
 /bin/bash
```

2) Run the following command to setup PATH, before building the patch

```
PATH="/usr/lib64/ccache:/opt/rh/devtoolset-2/root/usr/bin:$PATH"
```

Make sure that GCC version is greater than `4.8`

3) Clone the Github node repository and apply the patch :

Make sure that the `<version>`  is a Stable version (like `v4.8.4`), not a interim release like `v4.8.4-pre`, `v6.0.0-alpha.1` etc. The GYP build compares the versions and downloads the binary again during the `npm install` process.

```
> git clone git://github.com/nodejs/node
> cd node
> git checkout <version>
> curl -L <patch> | git apply (or make the necessary changes)
```

4)  Configure and make

```
> ./configure
> make -j9
```

5) The node binary can be found at `out/Release/node`

For example:

```
> out/Release/node -v
v6.11.0
```

Make sure that the version is a stable version like `v6.11.0`, not an interim release like `v4.8.4-pre`, `v6.0.0-alpha.1` etc. The GYP build compares the versions and downloads the binary again during the `npm install` process.

6) Run tests on the binary

```
> make test
```

### To release the patch version

- Send a PR to [node-install-pp](https://github.paypal.com/NodeOps/node-install-pp) after creating a new branch for the patch. Lets say `v6.11.0-pp-patch-062817`

Example PR - [here](https://github.paypal.com/NodeOps/node-install-pp/pull/10)

- Once the PR gets approved, Node.js builds can select a Node.js version by setting the following build properties (Only of the patch is not the default version):

`HARD_SELECT_VERSION` to `true`

`NODE_VERSION` to <patch_version>  like `v6.11.0-pp-patch-062817`
