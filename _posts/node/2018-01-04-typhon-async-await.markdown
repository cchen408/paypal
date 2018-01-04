---
layout: post
title:  "Async Await in Typhon"
date:   2018-01-04 10:23:41 -0700
categories: Node
---

### Typhon onconfig

Previously your ***onconfig*** would look something like this:

```
function(config, next){
  // do some stuff
  next(null, config);
}
```

Now with async await simply return config instead of using next.

```
async function(config){
  // do some stuff
  return config;
}
```

### Error handling with a wrapper function

With this wrap function there were two benefits:

1. Don't have to have a bunch of try catches everywhere.
2. You can simply throw errors in your code without having to worry about using next.

```
/**
* wrap function
**/
function co(fn) {
  return async function () {
    try {
      var next = _.last(arguments);
      await fn.apply(this, arguments);
    }
    catch (error) {
      next(error);
    }
  };
}
```

With this function I simply wrap all of my handler functions

```
exports.get = util.co(async function(req, res, next){

  var  record = await dbCall();
  if(!record) {
    // throw not found error
  }

});
```

Any errors thrown will be routed to the error middleware that you've written.