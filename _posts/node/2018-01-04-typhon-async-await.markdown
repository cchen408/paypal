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