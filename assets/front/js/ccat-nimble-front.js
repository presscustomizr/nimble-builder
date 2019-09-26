//     Underscore.js 1.9.1
//     http://underscorejs.org
//     (c) 2009-2018 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
//     Underscore may be freely distributed under the MIT license.
//
//     Modified by Nicolas GUILLAUME => replace the globally exposed underscore object window._ by window._utils_
//     => fixes issues generated when plugins are using different versions of underscore
//     @see https://github.com/presscustomizr/nimble-builder/issues/221
(function() {
  // Baseline setup
  // --------------

  // Establish the root object, `window` (`self`) in the browser, `global`
  // on the server, or `this` in some virtual machines. We use `self`
  // instead of `window` for `WebWorker` support.
  var root = typeof self == 'object' && self.self === self && self ||
            typeof global == 'object' && global.global === global && global ||
            this ||
            {};

  // Save the previous value of the `_` variable.
  var previousUnderscore = root._;

  // Save bytes in the minified (but not gzipped) version:
  var ArrayProto = Array.prototype, ObjProto = Object.prototype;
  var SymbolProto = typeof Symbol !== 'undefined' ? Symbol.prototype : null;

  // Create quick reference variables for speed access to core prototypes.
  var push = ArrayProto.push,
      slice = ArrayProto.slice,
      toString = ObjProto.toString,
      hasOwnProperty = ObjProto.hasOwnProperty;

  // All **ECMAScript 5** native function implementations that we hope to use
  // are declared here.
  var nativeIsArray = Array.isArray,
      nativeKeys = Object.keys,
      nativeCreate = Object.create;

  // Naked function reference for surrogate-prototype-swapping.
  var Ctor = function(){};

  // Create a safe reference to the Underscore object for use below.
  var _ = function(obj) {
    if (obj instanceof _) return obj;
    if (!(this instanceof _)) return new _(obj);
    this._wrapped = obj;
  };

  // Export the Underscore object for **Node.js**, with
  // backwards-compatibility for their old module API. If we're in
  // the browser, add `_` as a global object.
  // (`nodeType` is checked to ensure that `module`
  // and `exports` are not HTML elements.)
  if (typeof exports != 'undefined' && !exports.nodeType) {
    if (typeof module != 'undefined' && !module.nodeType && module.exports) {
      exports = module.exports = _;
    }
    // modification by @nikeo, November 2, 2018
    exports._utils_ = _;
  } else {
    // modification by @nikeo, November 2, 2018
    root._utils_ = _;
  }

  // Current version.
  _.VERSION = '1.9.1';

  // Internal function that returns an efficient (for current engines) version
  // of the passed-in callback, to be repeatedly applied in other Underscore
  // functions.
  var optimizeCb = function(func, context, argCount) {
    if (context === void 0) return func;
    switch (argCount == null ? 3 : argCount) {
      case 1: return function(value) {
        return func.call(context, value);
      };
      // The 2-argument case is omitted because we’re not using it.
      case 3: return function(value, index, collection) {
        return func.call(context, value, index, collection);
      };
      case 4: return function(accumulator, value, index, collection) {
        return func.call(context, accumulator, value, index, collection);
      };
    }
    return function() {
      return func.apply(context, arguments);
    };
  };

  var builtinIteratee;

  // An internal function to generate callbacks that can be applied to each
  // element in a collection, returning the desired result — either `identity`,
  // an arbitrary callback, a property matcher, or a property accessor.
  var cb = function(value, context, argCount) {
    if (_.iteratee !== builtinIteratee) return _.iteratee(value, context);
    if (value == null) return _.identity;
    if (_.isFunction(value)) return optimizeCb(value, context, argCount);
    if (_.isObject(value) && !_.isArray(value)) return _.matcher(value);
    return _.property(value);
  };

  // External wrapper for our callback generator. Users may customize
  // `_.iteratee` if they want additional predicate/iteratee shorthand styles.
  // This abstraction hides the internal-only argCount argument.
  _.iteratee = builtinIteratee = function(value, context) {
    return cb(value, context, Infinity);
  };

  // Some functions take a variable number of arguments, or a few expected
  // arguments at the beginning and then a variable number of values to operate
  // on. This helper accumulates all remaining arguments past the function’s
  // argument length (or an explicit `startIndex`), into an array that becomes
  // the last argument. Similar to ES6’s "rest parameter".
  var restArguments = function(func, startIndex) {
    startIndex = startIndex == null ? func.length - 1 : +startIndex;
    return function() {
      var length = Math.max(arguments.length - startIndex, 0),
          rest = Array(length),
          index = 0;
      for (; index < length; index++) {
        rest[index] = arguments[index + startIndex];
      }
      switch (startIndex) {
        case 0: return func.call(this, rest);
        case 1: return func.call(this, arguments[0], rest);
        case 2: return func.call(this, arguments[0], arguments[1], rest);
      }
      var args = Array(startIndex + 1);
      for (index = 0; index < startIndex; index++) {
        args[index] = arguments[index];
      }
      args[startIndex] = rest;
      return func.apply(this, args);
    };
  };

  // An internal function for creating a new object that inherits from another.
  var baseCreate = function(prototype) {
    if (!_.isObject(prototype)) return {};
    if (nativeCreate) return nativeCreate(prototype);
    Ctor.prototype = prototype;
    var result = new Ctor;
    Ctor.prototype = null;
    return result;
  };

  var shallowProperty = function(key) {
    return function(obj) {
      return obj == null ? void 0 : obj[key];
    };
  };

  var has = function(obj, path) {
    return obj != null && hasOwnProperty.call(obj, path);
  }

  var deepGet = function(obj, path) {
    var length = path.length;
    for (var i = 0; i < length; i++) {
      if (obj == null) return void 0;
      obj = obj[path[i]];
    }
    return length ? obj : void 0;
  };

  // Helper for collection methods to determine whether a collection
  // should be iterated as an array or as an object.
  // Related: http://people.mozilla.org/~jorendorff/es6-draft.html#sec-tolength
  // Avoids a very nasty iOS 8 JIT bug on ARM-64. #2094
  var MAX_ARRAY_INDEX = Math.pow(2, 53) - 1;
  var getLength = shallowProperty('length');
  var isArrayLike = function(collection) {
    var length = getLength(collection);
    return typeof length == 'number' && length >= 0 && length <= MAX_ARRAY_INDEX;
  };

  // Collection Functions
  // --------------------

  // The cornerstone, an `each` implementation, aka `forEach`.
  // Handles raw objects in addition to array-likes. Treats all
  // sparse array-likes as if they were dense.
  _.each = _.forEach = function(obj, iteratee, context) {
    iteratee = optimizeCb(iteratee, context);
    var i, length;
    if (isArrayLike(obj)) {
      for (i = 0, length = obj.length; i < length; i++) {
        iteratee(obj[i], i, obj);
      }
    } else {
      var keys = _.keys(obj);
      for (i = 0, length = keys.length; i < length; i++) {
        iteratee(obj[keys[i]], keys[i], obj);
      }
    }
    return obj;
  };

  // Return the results of applying the iteratee to each element.
  _.map = _.collect = function(obj, iteratee, context) {
    iteratee = cb(iteratee, context);
    var keys = !isArrayLike(obj) && _.keys(obj),
        length = (keys || obj).length,
        results = Array(length);
    for (var index = 0; index < length; index++) {
      var currentKey = keys ? keys[index] : index;
      results[index] = iteratee(obj[currentKey], currentKey, obj);
    }
    return results;
  };

  // Create a reducing function iterating left or right.
  var createReduce = function(dir) {
    // Wrap code that reassigns argument variables in a separate function than
    // the one that accesses `arguments.length` to avoid a perf hit. (#1991)
    var reducer = function(obj, iteratee, memo, initial) {
      var keys = !isArrayLike(obj) && _.keys(obj),
          length = (keys || obj).length,
          index = dir > 0 ? 0 : length - 1;
      if (!initial) {
        memo = obj[keys ? keys[index] : index];
        index += dir;
      }
      for (; index >= 0 && index < length; index += dir) {
        var currentKey = keys ? keys[index] : index;
        memo = iteratee(memo, obj[currentKey], currentKey, obj);
      }
      return memo;
    };

    return function(obj, iteratee, memo, context) {
      var initial = arguments.length >= 3;
      return reducer(obj, optimizeCb(iteratee, context, 4), memo, initial);
    };
  };

  // **Reduce** builds up a single result from a list of values, aka `inject`,
  // or `foldl`.
  _.reduce = _.foldl = _.inject = createReduce(1);

  // The right-associative version of reduce, also known as `foldr`.
  _.reduceRight = _.foldr = createReduce(-1);

  // Return the first value which passes a truth test. Aliased as `detect`.
  _.find = _.detect = function(obj, predicate, context) {
    var keyFinder = isArrayLike(obj) ? _.findIndex : _.findKey;
    var key = keyFinder(obj, predicate, context);
    if (key !== void 0 && key !== -1) return obj[key];
  };

  // Return all the elements that pass a truth test.
  // Aliased as `select`.
  _.filter = _.select = function(obj, predicate, context) {
    var results = [];
    predicate = cb(predicate, context);
    _.each(obj, function(value, index, list) {
      if (predicate(value, index, list)) results.push(value);
    });
    return results;
  };

  // Return all the elements for which a truth test fails.
  _.reject = function(obj, predicate, context) {
    return _.filter(obj, _.negate(cb(predicate)), context);
  };

  // Determine whether all of the elements match a truth test.
  // Aliased as `all`.
  _.every = _.all = function(obj, predicate, context) {
    predicate = cb(predicate, context);
    var keys = !isArrayLike(obj) && _.keys(obj),
        length = (keys || obj).length;
    for (var index = 0; index < length; index++) {
      var currentKey = keys ? keys[index] : index;
      if (!predicate(obj[currentKey], currentKey, obj)) return false;
    }
    return true;
  };

  // Determine if at least one element in the object matches a truth test.
  // Aliased as `any`.
  _.some = _.any = function(obj, predicate, context) {
    predicate = cb(predicate, context);
    var keys = !isArrayLike(obj) && _.keys(obj),
        length = (keys || obj).length;
    for (var index = 0; index < length; index++) {
      var currentKey = keys ? keys[index] : index;
      if (predicate(obj[currentKey], currentKey, obj)) return true;
    }
    return false;
  };

  // Determine if the array or object contains a given item (using `===`).
  // Aliased as `includes` and `include`.
  _.contains = _.includes = _.include = function(obj, item, fromIndex, guard) {
    if (!isArrayLike(obj)) obj = _.values(obj);
    if (typeof fromIndex != 'number' || guard) fromIndex = 0;
    return _.indexOf(obj, item, fromIndex) >= 0;
  };

  // Invoke a method (with arguments) on every item in a collection.
  _.invoke = restArguments(function(obj, path, args) {
    var contextPath, func;
    if (_.isFunction(path)) {
      func = path;
    } else if (_.isArray(path)) {
      contextPath = path.slice(0, -1);
      path = path[path.length - 1];
    }
    return _.map(obj, function(context) {
      var method = func;
      if (!method) {
        if (contextPath && contextPath.length) {
          context = deepGet(context, contextPath);
        }
        if (context == null) return void 0;
        method = context[path];
      }
      return method == null ? method : method.apply(context, args);
    });
  });

  // Convenience version of a common use case of `map`: fetching a property.
  _.pluck = function(obj, key) {
    return _.map(obj, _.property(key));
  };

  // Convenience version of a common use case of `filter`: selecting only objects
  // containing specific `key:value` pairs.
  _.where = function(obj, attrs) {
    return _.filter(obj, _.matcher(attrs));
  };

  // Convenience version of a common use case of `find`: getting the first object
  // containing specific `key:value` pairs.
  _.findWhere = function(obj, attrs) {
    return _.find(obj, _.matcher(attrs));
  };

  // Return the maximum element (or element-based computation).
  _.max = function(obj, iteratee, context) {
    var result = -Infinity, lastComputed = -Infinity,
        value, computed;
    if (iteratee == null || typeof iteratee == 'number' && typeof obj[0] != 'object' && obj != null) {
      obj = isArrayLike(obj) ? obj : _.values(obj);
      for (var i = 0, length = obj.length; i < length; i++) {
        value = obj[i];
        if (value != null && value > result) {
          result = value;
        }
      }
    } else {
      iteratee = cb(iteratee, context);
      _.each(obj, function(v, index, list) {
        computed = iteratee(v, index, list);
        if (computed > lastComputed || computed === -Infinity && result === -Infinity) {
          result = v;
          lastComputed = computed;
        }
      });
    }
    return result;
  };

  // Return the minimum element (or element-based computation).
  _.min = function(obj, iteratee, context) {
    var result = Infinity, lastComputed = Infinity,
        value, computed;
    if (iteratee == null || typeof iteratee == 'number' && typeof obj[0] != 'object' && obj != null) {
      obj = isArrayLike(obj) ? obj : _.values(obj);
      for (var i = 0, length = obj.length; i < length; i++) {
        value = obj[i];
        if (value != null && value < result) {
          result = value;
        }
      }
    } else {
      iteratee = cb(iteratee, context);
      _.each(obj, function(v, index, list) {
        computed = iteratee(v, index, list);
        if (computed < lastComputed || computed === Infinity && result === Infinity) {
          result = v;
          lastComputed = computed;
        }
      });
    }
    return result;
  };

  // Shuffle a collection.
  _.shuffle = function(obj) {
    return _.sample(obj, Infinity);
  };

  // Sample **n** random values from a collection using the modern version of the
  // [Fisher-Yates shuffle](http://en.wikipedia.org/wiki/Fisher–Yates_shuffle).
  // If **n** is not specified, returns a single random element.
  // The internal `guard` argument allows it to work with `map`.
  _.sample = function(obj, n, guard) {
    if (n == null || guard) {
      if (!isArrayLike(obj)) obj = _.values(obj);
      return obj[_.random(obj.length - 1)];
    }
    var sample = isArrayLike(obj) ? _.clone(obj) : _.values(obj);
    var length = getLength(sample);
    n = Math.max(Math.min(n, length), 0);
    var last = length - 1;
    for (var index = 0; index < n; index++) {
      var rand = _.random(index, last);
      var temp = sample[index];
      sample[index] = sample[rand];
      sample[rand] = temp;
    }
    return sample.slice(0, n);
  };

  // Sort the object's values by a criterion produced by an iteratee.
  _.sortBy = function(obj, iteratee, context) {
    var index = 0;
    iteratee = cb(iteratee, context);
    return _.pluck(_.map(obj, function(value, key, list) {
      return {
        value: value,
        index: index++,
        criteria: iteratee(value, key, list)
      };
    }).sort(function(left, right) {
      var a = left.criteria;
      var b = right.criteria;
      if (a !== b) {
        if (a > b || a === void 0) return 1;
        if (a < b || b === void 0) return -1;
      }
      return left.index - right.index;
    }), 'value');
  };

  // An internal function used for aggregate "group by" operations.
  var group = function(behavior, partition) {
    return function(obj, iteratee, context) {
      var result = partition ? [[], []] : {};
      iteratee = cb(iteratee, context);
      _.each(obj, function(value, index) {
        var key = iteratee(value, index, obj);
        behavior(result, value, key);
      });
      return result;
    };
  };

  // Groups the object's values by a criterion. Pass either a string attribute
  // to group by, or a function that returns the criterion.
  _.groupBy = group(function(result, value, key) {
    if (has(result, key)) result[key].push(value); else result[key] = [value];
  });

  // Indexes the object's values by a criterion, similar to `groupBy`, but for
  // when you know that your index values will be unique.
  _.indexBy = group(function(result, value, key) {
    result[key] = value;
  });

  // Counts instances of an object that group by a certain criterion. Pass
  // either a string attribute to count by, or a function that returns the
  // criterion.
  _.countBy = group(function(result, value, key) {
    if (has(result, key)) result[key]++; else result[key] = 1;
  });

  var reStrSymbol = /[^\ud800-\udfff]|[\ud800-\udbff][\udc00-\udfff]|[\ud800-\udfff]/g;
  // Safely create a real, live array from anything iterable.
  _.toArray = function(obj) {
    if (!obj) return [];
    if (_.isArray(obj)) return slice.call(obj);
    if (_.isString(obj)) {
      // Keep surrogate pair characters together
      return obj.match(reStrSymbol);
    }
    if (isArrayLike(obj)) return _.map(obj, _.identity);
    return _.values(obj);
  };

  // Return the number of elements in an object.
  _.size = function(obj) {
    if (obj == null) return 0;
    return isArrayLike(obj) ? obj.length : _.keys(obj).length;
  };

  // Split a collection into two arrays: one whose elements all satisfy the given
  // predicate, and one whose elements all do not satisfy the predicate.
  _.partition = group(function(result, value, pass) {
    result[pass ? 0 : 1].push(value);
  }, true);

  // Array Functions
  // ---------------

  // Get the first element of an array. Passing **n** will return the first N
  // values in the array. Aliased as `head` and `take`. The **guard** check
  // allows it to work with `_.map`.
  _.first = _.head = _.take = function(array, n, guard) {
    if (array == null || array.length < 1) return n == null ? void 0 : [];
    if (n == null || guard) return array[0];
    return _.initial(array, array.length - n);
  };

  // Returns everything but the last entry of the array. Especially useful on
  // the arguments object. Passing **n** will return all the values in
  // the array, excluding the last N.
  _.initial = function(array, n, guard) {
    return slice.call(array, 0, Math.max(0, array.length - (n == null || guard ? 1 : n)));
  };

  // Get the last element of an array. Passing **n** will return the last N
  // values in the array.
  _.last = function(array, n, guard) {
    if (array == null || array.length < 1) return n == null ? void 0 : [];
    if (n == null || guard) return array[array.length - 1];
    return _.rest(array, Math.max(0, array.length - n));
  };

  // Returns everything but the first entry of the array. Aliased as `tail` and `drop`.
  // Especially useful on the arguments object. Passing an **n** will return
  // the rest N values in the array.
  _.rest = _.tail = _.drop = function(array, n, guard) {
    return slice.call(array, n == null || guard ? 1 : n);
  };

  // Trim out all falsy values from an array.
  _.compact = function(array) {
    return _.filter(array, Boolean);
  };

  // Internal implementation of a recursive `flatten` function.
  var flatten = function(input, shallow, strict, output) {
    output = output || [];
    var idx = output.length;
    for (var i = 0, length = getLength(input); i < length; i++) {
      var value = input[i];
      if (isArrayLike(value) && (_.isArray(value) || _.isArguments(value))) {
        // Flatten current level of array or arguments object.
        if (shallow) {
          var j = 0, len = value.length;
          while (j < len) output[idx++] = value[j++];
        } else {
          flatten(value, shallow, strict, output);
          idx = output.length;
        }
      } else if (!strict) {
        output[idx++] = value;
      }
    }
    return output;
  };

  // Flatten out an array, either recursively (by default), or just one level.
  _.flatten = function(array, shallow) {
    return flatten(array, shallow, false);
  };

  // Return a version of the array that does not contain the specified value(s).
  _.without = restArguments(function(array, otherArrays) {
    return _.difference(array, otherArrays);
  });

  // Produce a duplicate-free version of the array. If the array has already
  // been sorted, you have the option of using a faster algorithm.
  // The faster algorithm will not work with an iteratee if the iteratee
  // is not a one-to-one function, so providing an iteratee will disable
  // the faster algorithm.
  // Aliased as `unique`.
  _.uniq = _.unique = function(array, isSorted, iteratee, context) {
    if (!_.isBoolean(isSorted)) {
      context = iteratee;
      iteratee = isSorted;
      isSorted = false;
    }
    if (iteratee != null) iteratee = cb(iteratee, context);
    var result = [];
    var seen = [];
    for (var i = 0, length = getLength(array); i < length; i++) {
      var value = array[i],
          computed = iteratee ? iteratee(value, i, array) : value;
      if (isSorted && !iteratee) {
        if (!i || seen !== computed) result.push(value);
        seen = computed;
      } else if (iteratee) {
        if (!_.contains(seen, computed)) {
          seen.push(computed);
          result.push(value);
        }
      } else if (!_.contains(result, value)) {
        result.push(value);
      }
    }
    return result;
  };

  // Produce an array that contains the union: each distinct element from all of
  // the passed-in arrays.
  _.union = restArguments(function(arrays) {
    return _.uniq(flatten(arrays, true, true));
  });

  // Produce an array that contains every item shared between all the
  // passed-in arrays.
  _.intersection = function(array) {
    var result = [];
    var argsLength = arguments.length;
    for (var i = 0, length = getLength(array); i < length; i++) {
      var item = array[i];
      if (_.contains(result, item)) continue;
      var j;
      for (j = 1; j < argsLength; j++) {
        if (!_.contains(arguments[j], item)) break;
      }
      if (j === argsLength) result.push(item);
    }
    return result;
  };

  // Take the difference between one array and a number of other arrays.
  // Only the elements present in just the first array will remain.
  _.difference = restArguments(function(array, rest) {
    rest = flatten(rest, true, true);
    return _.filter(array, function(value){
      return !_.contains(rest, value);
    });
  });

  // Complement of _.zip. Unzip accepts an array of arrays and groups
  // each array's elements on shared indices.
  _.unzip = function(array) {
    var length = array && _.max(array, getLength).length || 0;
    var result = Array(length);

    for (var index = 0; index < length; index++) {
      result[index] = _.pluck(array, index);
    }
    return result;
  };

  // Zip together multiple lists into a single array -- elements that share
  // an index go together.
  _.zip = restArguments(_.unzip);

  // Converts lists into objects. Pass either a single array of `[key, value]`
  // pairs, or two parallel arrays of the same length -- one of keys, and one of
  // the corresponding values. Passing by pairs is the reverse of _.pairs.
  _.object = function(list, values) {
    var result = {};
    for (var i = 0, length = getLength(list); i < length; i++) {
      if (values) {
        result[list[i]] = values[i];
      } else {
        result[list[i][0]] = list[i][1];
      }
    }
    return result;
  };

  // Generator function to create the findIndex and findLastIndex functions.
  var createPredicateIndexFinder = function(dir) {
    return function(array, predicate, context) {
      predicate = cb(predicate, context);
      var length = getLength(array);
      var index = dir > 0 ? 0 : length - 1;
      for (; index >= 0 && index < length; index += dir) {
        if (predicate(array[index], index, array)) return index;
      }
      return -1;
    };
  };

  // Returns the first index on an array-like that passes a predicate test.
  _.findIndex = createPredicateIndexFinder(1);
  _.findLastIndex = createPredicateIndexFinder(-1);

  // Use a comparator function to figure out the smallest index at which
  // an object should be inserted so as to maintain order. Uses binary search.
  _.sortedIndex = function(array, obj, iteratee, context) {
    iteratee = cb(iteratee, context, 1);
    var value = iteratee(obj);
    var low = 0, high = getLength(array);
    while (low < high) {
      var mid = Math.floor((low + high) / 2);
      if (iteratee(array[mid]) < value) low = mid + 1; else high = mid;
    }
    return low;
  };

  // Generator function to create the indexOf and lastIndexOf functions.
  var createIndexFinder = function(dir, predicateFind, sortedIndex) {
    return function(array, item, idx) {
      var i = 0, length = getLength(array);
      if (typeof idx == 'number') {
        if (dir > 0) {
          i = idx >= 0 ? idx : Math.max(idx + length, i);
        } else {
          length = idx >= 0 ? Math.min(idx + 1, length) : idx + length + 1;
        }
      } else if (sortedIndex && idx && length) {
        idx = sortedIndex(array, item);
        return array[idx] === item ? idx : -1;
      }
      if (item !== item) {
        idx = predicateFind(slice.call(array, i, length), _.isNaN);
        return idx >= 0 ? idx + i : -1;
      }
      for (idx = dir > 0 ? i : length - 1; idx >= 0 && idx < length; idx += dir) {
        if (array[idx] === item) return idx;
      }
      return -1;
    };
  };

  // Return the position of the first occurrence of an item in an array,
  // or -1 if the item is not included in the array.
  // If the array is large and already in sort order, pass `true`
  // for **isSorted** to use binary search.
  _.indexOf = createIndexFinder(1, _.findIndex, _.sortedIndex);
  _.lastIndexOf = createIndexFinder(-1, _.findLastIndex);

  // Generate an integer Array containing an arithmetic progression. A port of
  // the native Python `range()` function. See
  // [the Python documentation](http://docs.python.org/library/functions.html#range).
  _.range = function(start, stop, step) {
    if (stop == null) {
      stop = start || 0;
      start = 0;
    }
    if (!step) {
      step = stop < start ? -1 : 1;
    }

    var length = Math.max(Math.ceil((stop - start) / step), 0);
    var range = Array(length);

    for (var idx = 0; idx < length; idx++, start += step) {
      range[idx] = start;
    }

    return range;
  };

  // Chunk a single array into multiple arrays, each containing `count` or fewer
  // items.
  _.chunk = function(array, count) {
    if (count == null || count < 1) return [];
    var result = [];
    var i = 0, length = array.length;
    while (i < length) {
      result.push(slice.call(array, i, i += count));
    }
    return result;
  };

  // Function (ahem) Functions
  // ------------------

  // Determines whether to execute a function as a constructor
  // or a normal function with the provided arguments.
  var executeBound = function(sourceFunc, boundFunc, context, callingContext, args) {
    if (!(callingContext instanceof boundFunc)) return sourceFunc.apply(context, args);
    var self = baseCreate(sourceFunc.prototype);
    var result = sourceFunc.apply(self, args);
    if (_.isObject(result)) return result;
    return self;
  };

  // Create a function bound to a given object (assigning `this`, and arguments,
  // optionally). Delegates to **ECMAScript 5**'s native `Function.bind` if
  // available.
  _.bind = restArguments(function(func, context, args) {
    if (!_.isFunction(func)) throw new TypeError('Bind must be called on a function');
    var bound = restArguments(function(callArgs) {
      return executeBound(func, bound, context, this, args.concat(callArgs));
    });
    return bound;
  });

  // Partially apply a function by creating a version that has had some of its
  // arguments pre-filled, without changing its dynamic `this` context. _ acts
  // as a placeholder by default, allowing any combination of arguments to be
  // pre-filled. Set `_.partial.placeholder` for a custom placeholder argument.
  _.partial = restArguments(function(func, boundArgs) {
    var placeholder = _.partial.placeholder;
    var bound = function() {
      var position = 0, length = boundArgs.length;
      var args = Array(length);
      for (var i = 0; i < length; i++) {
        args[i] = boundArgs[i] === placeholder ? arguments[position++] : boundArgs[i];
      }
      while (position < arguments.length) args.push(arguments[position++]);
      return executeBound(func, bound, this, this, args);
    };
    return bound;
  });

  _.partial.placeholder = _;

  // Bind a number of an object's methods to that object. Remaining arguments
  // are the method names to be bound. Useful for ensuring that all callbacks
  // defined on an object belong to it.
  _.bindAll = restArguments(function(obj, keys) {
    keys = flatten(keys, false, false);
    var index = keys.length;
    if (index < 1) throw new Error('bindAll must be passed function names');
    while (index--) {
      var key = keys[index];
      obj[key] = _.bind(obj[key], obj);
    }
  });

  // Memoize an expensive function by storing its results.
  _.memoize = function(func, hasher) {
    var memoize = function(key) {
      var cache = memoize.cache;
      var address = '' + (hasher ? hasher.apply(this, arguments) : key);
      if (!has(cache, address)) cache[address] = func.apply(this, arguments);
      return cache[address];
    };
    memoize.cache = {};
    return memoize;
  };

  // Delays a function for the given number of milliseconds, and then calls
  // it with the arguments supplied.
  _.delay = restArguments(function(func, wait, args) {
    return setTimeout(function() {
      return func.apply(null, args);
    }, wait);
  });

  // Defers a function, scheduling it to run after the current call stack has
  // cleared.
  _.defer = _.partial(_.delay, _, 1);

  // Returns a function, that, when invoked, will only be triggered at most once
  // during a given window of time. Normally, the throttled function will run
  // as much as it can, without ever going more than once per `wait` duration;
  // but if you'd like to disable the execution on the leading edge, pass
  // `{leading: false}`. To disable execution on the trailing edge, ditto.
  _.throttle = function(func, wait, options) {
    var timeout, context, args, result;
    var previous = 0;
    if (!options) options = {};

    var later = function() {
      previous = options.leading === false ? 0 : _.now();
      timeout = null;
      result = func.apply(context, args);
      if (!timeout) context = args = null;
    };

    var throttled = function() {
      var now = _.now();
      if (!previous && options.leading === false) previous = now;
      var remaining = wait - (now - previous);
      context = this;
      args = arguments;
      if (remaining <= 0 || remaining > wait) {
        if (timeout) {
          clearTimeout(timeout);
          timeout = null;
        }
        previous = now;
        result = func.apply(context, args);
        if (!timeout) context = args = null;
      } else if (!timeout && options.trailing !== false) {
        timeout = setTimeout(later, remaining);
      }
      return result;
    };

    throttled.cancel = function() {
      clearTimeout(timeout);
      previous = 0;
      timeout = context = args = null;
    };

    return throttled;
  };

  // Returns a function, that, as long as it continues to be invoked, will not
  // be triggered. The function will be called after it stops being called for
  // N milliseconds. If `immediate` is passed, trigger the function on the
  // leading edge, instead of the trailing.
  _.debounce = function(func, wait, immediate) {
    var timeout, result;

    var later = function(context, args) {
      timeout = null;
      if (args) result = func.apply(context, args);
    };

    var debounced = restArguments(function(args) {
      if (timeout) clearTimeout(timeout);
      if (immediate) {
        var callNow = !timeout;
        timeout = setTimeout(later, wait);
        if (callNow) result = func.apply(this, args);
      } else {
        timeout = _.delay(later, wait, this, args);
      }

      return result;
    });

    debounced.cancel = function() {
      clearTimeout(timeout);
      timeout = null;
    };

    return debounced;
  };

  // Returns the first function passed as an argument to the second,
  // allowing you to adjust arguments, run code before and after, and
  // conditionally execute the original function.
  _.wrap = function(func, wrapper) {
    return _.partial(wrapper, func);
  };

  // Returns a negated version of the passed-in predicate.
  _.negate = function(predicate) {
    return function() {
      return !predicate.apply(this, arguments);
    };
  };

  // Returns a function that is the composition of a list of functions, each
  // consuming the return value of the function that follows.
  _.compose = function() {
    var args = arguments;
    var start = args.length - 1;
    return function() {
      var i = start;
      var result = args[start].apply(this, arguments);
      while (i--) result = args[i].call(this, result);
      return result;
    };
  };

  // Returns a function that will only be executed on and after the Nth call.
  _.after = function(times, func) {
    return function() {
      if (--times < 1) {
        return func.apply(this, arguments);
      }
    };
  };

  // Returns a function that will only be executed up to (but not including) the Nth call.
  _.before = function(times, func) {
    var memo;
    return function() {
      if (--times > 0) {
        memo = func.apply(this, arguments);
      }
      if (times <= 1) func = null;
      return memo;
    };
  };

  // Returns a function that will be executed at most one time, no matter how
  // often you call it. Useful for lazy initialization.
  _.once = _.partial(_.before, 2);

  _.restArguments = restArguments;

  // Object Functions
  // ----------------

  // Keys in IE < 9 that won't be iterated by `for key in ...` and thus missed.
  var hasEnumBug = !{toString: null}.propertyIsEnumerable('toString');
  var nonEnumerableProps = ['valueOf', 'isPrototypeOf', 'toString',
    'propertyIsEnumerable', 'hasOwnProperty', 'toLocaleString'];

  var collectNonEnumProps = function(obj, keys) {
    var nonEnumIdx = nonEnumerableProps.length;
    var constructor = obj.constructor;
    var proto = _.isFunction(constructor) && constructor.prototype || ObjProto;

    // Constructor is a special case.
    var prop = 'constructor';
    if (has(obj, prop) && !_.contains(keys, prop)) keys.push(prop);

    while (nonEnumIdx--) {
      prop = nonEnumerableProps[nonEnumIdx];
      if (prop in obj && obj[prop] !== proto[prop] && !_.contains(keys, prop)) {
        keys.push(prop);
      }
    }
  };

  // Retrieve the names of an object's own properties.
  // Delegates to **ECMAScript 5**'s native `Object.keys`.
  _.keys = function(obj) {
    if (!_.isObject(obj)) return [];
    if (nativeKeys) return nativeKeys(obj);
    var keys = [];
    for (var key in obj) if (has(obj, key)) keys.push(key);
    // Ahem, IE < 9.
    if (hasEnumBug) collectNonEnumProps(obj, keys);
    return keys;
  };

  // Retrieve all the property names of an object.
  _.allKeys = function(obj) {
    if (!_.isObject(obj)) return [];
    var keys = [];
    for (var key in obj) keys.push(key);
    // Ahem, IE < 9.
    if (hasEnumBug) collectNonEnumProps(obj, keys);
    return keys;
  };

  // Retrieve the values of an object's properties.
  _.values = function(obj) {
    var keys = _.keys(obj);
    var length = keys.length;
    var values = Array(length);
    for (var i = 0; i < length; i++) {
      values[i] = obj[keys[i]];
    }
    return values;
  };

  // Returns the results of applying the iteratee to each element of the object.
  // In contrast to _.map it returns an object.
  _.mapObject = function(obj, iteratee, context) {
    iteratee = cb(iteratee, context);
    var keys = _.keys(obj),
        length = keys.length,
        results = {};
    for (var index = 0; index < length; index++) {
      var currentKey = keys[index];
      results[currentKey] = iteratee(obj[currentKey], currentKey, obj);
    }
    return results;
  };

  // Convert an object into a list of `[key, value]` pairs.
  // The opposite of _.object.
  _.pairs = function(obj) {
    var keys = _.keys(obj);
    var length = keys.length;
    var pairs = Array(length);
    for (var i = 0; i < length; i++) {
      pairs[i] = [keys[i], obj[keys[i]]];
    }
    return pairs;
  };

  // Invert the keys and values of an object. The values must be serializable.
  _.invert = function(obj) {
    var result = {};
    var keys = _.keys(obj);
    for (var i = 0, length = keys.length; i < length; i++) {
      result[obj[keys[i]]] = keys[i];
    }
    return result;
  };

  // Return a sorted list of the function names available on the object.
  // Aliased as `methods`.
  _.functions = _.methods = function(obj) {
    var names = [];
    for (var key in obj) {
      if (_.isFunction(obj[key])) names.push(key);
    }
    return names.sort();
  };

  // An internal function for creating assigner functions.
  var createAssigner = function(keysFunc, defaults) {
    return function(obj) {
      var length = arguments.length;
      if (defaults) obj = Object(obj);
      if (length < 2 || obj == null) return obj;
      for (var index = 1; index < length; index++) {
        var source = arguments[index],
            keys = keysFunc(source),
            l = keys.length;
        for (var i = 0; i < l; i++) {
          var key = keys[i];
          if (!defaults || obj[key] === void 0) obj[key] = source[key];
        }
      }
      return obj;
    };
  };

  // Extend a given object with all the properties in passed-in object(s).
  _.extend = createAssigner(_.allKeys);

  // Assigns a given object with all the own properties in the passed-in object(s).
  // (https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/Object/assign)
  _.extendOwn = _.assign = createAssigner(_.keys);

  // Returns the first key on an object that passes a predicate test.
  _.findKey = function(obj, predicate, context) {
    predicate = cb(predicate, context);
    var keys = _.keys(obj), key;
    for (var i = 0, length = keys.length; i < length; i++) {
      key = keys[i];
      if (predicate(obj[key], key, obj)) return key;
    }
  };

  // Internal pick helper function to determine if `obj` has key `key`.
  var keyInObj = function(value, key, obj) {
    return key in obj;
  };

  // Return a copy of the object only containing the whitelisted properties.
  _.pick = restArguments(function(obj, keys) {
    var result = {}, iteratee = keys[0];
    if (obj == null) return result;
    if (_.isFunction(iteratee)) {
      if (keys.length > 1) iteratee = optimizeCb(iteratee, keys[1]);
      keys = _.allKeys(obj);
    } else {
      iteratee = keyInObj;
      keys = flatten(keys, false, false);
      obj = Object(obj);
    }
    for (var i = 0, length = keys.length; i < length; i++) {
      var key = keys[i];
      var value = obj[key];
      if (iteratee(value, key, obj)) result[key] = value;
    }
    return result;
  });

  // Return a copy of the object without the blacklisted properties.
  _.omit = restArguments(function(obj, keys) {
    var iteratee = keys[0], context;
    if (_.isFunction(iteratee)) {
      iteratee = _.negate(iteratee);
      if (keys.length > 1) context = keys[1];
    } else {
      keys = _.map(flatten(keys, false, false), String);
      iteratee = function(value, key) {
        return !_.contains(keys, key);
      };
    }
    return _.pick(obj, iteratee, context);
  });

  // Fill in a given object with default properties.
  _.defaults = createAssigner(_.allKeys, true);

  // Creates an object that inherits from the given prototype object.
  // If additional properties are provided then they will be added to the
  // created object.
  _.create = function(prototype, props) {
    var result = baseCreate(prototype);
    if (props) _.extendOwn(result, props);
    return result;
  };

  // Create a (shallow-cloned) duplicate of an object.
  _.clone = function(obj) {
    if (!_.isObject(obj)) return obj;
    return _.isArray(obj) ? obj.slice() : _.extend({}, obj);
  };

  // Invokes interceptor with the obj, and then returns obj.
  // The primary purpose of this method is to "tap into" a method chain, in
  // order to perform operations on intermediate results within the chain.
  _.tap = function(obj, interceptor) {
    interceptor(obj);
    return obj;
  };

  // Returns whether an object has a given set of `key:value` pairs.
  _.isMatch = function(object, attrs) {
    var keys = _.keys(attrs), length = keys.length;
    if (object == null) return !length;
    var obj = Object(object);
    for (var i = 0; i < length; i++) {
      var key = keys[i];
      if (attrs[key] !== obj[key] || !(key in obj)) return false;
    }
    return true;
  };


  // Internal recursive comparison function for `isEqual`.
  var eq, deepEq;
  eq = function(a, b, aStack, bStack) {
    // Identical objects are equal. `0 === -0`, but they aren't identical.
    // See the [Harmony `egal` proposal](http://wiki.ecmascript.org/doku.php?id=harmony:egal).
    if (a === b) return a !== 0 || 1 / a === 1 / b;
    // `null` or `undefined` only equal to itself (strict comparison).
    if (a == null || b == null) return false;
    // `NaN`s are equivalent, but non-reflexive.
    if (a !== a) return b !== b;
    // Exhaust primitive checks
    var type = typeof a;
    if (type !== 'function' && type !== 'object' && typeof b != 'object') return false;
    return deepEq(a, b, aStack, bStack);
  };

  // Internal recursive comparison function for `isEqual`.
  deepEq = function(a, b, aStack, bStack) {
    // Unwrap any wrapped objects.
    if (a instanceof _) a = a._wrapped;
    if (b instanceof _) b = b._wrapped;
    // Compare `[[Class]]` names.
    var className = toString.call(a);
    if (className !== toString.call(b)) return false;
    switch (className) {
      // Strings, numbers, regular expressions, dates, and booleans are compared by value.
      case '[object RegExp]':
      // RegExps are coerced to strings for comparison (Note: '' + /a/i === '/a/i')
      case '[object String]':
        // Primitives and their corresponding object wrappers are equivalent; thus, `"5"` is
        // equivalent to `new String("5")`.
        return '' + a === '' + b;
      case '[object Number]':
        // `NaN`s are equivalent, but non-reflexive.
        // Object(NaN) is equivalent to NaN.
        if (+a !== +a) return +b !== +b;
        // An `egal` comparison is performed for other numeric values.
        return +a === 0 ? 1 / +a === 1 / b : +a === +b;
      case '[object Date]':
      case '[object Boolean]':
        // Coerce dates and booleans to numeric primitive values. Dates are compared by their
        // millisecond representations. Note that invalid dates with millisecond representations
        // of `NaN` are not equivalent.
        return +a === +b;
      case '[object Symbol]':
        return SymbolProto.valueOf.call(a) === SymbolProto.valueOf.call(b);
    }

    var areArrays = className === '[object Array]';
    if (!areArrays) {
      if (typeof a != 'object' || typeof b != 'object') return false;

      // Objects with different constructors are not equivalent, but `Object`s or `Array`s
      // from different frames are.
      var aCtor = a.constructor, bCtor = b.constructor;
      if (aCtor !== bCtor && !(_.isFunction(aCtor) && aCtor instanceof aCtor &&
                               _.isFunction(bCtor) && bCtor instanceof bCtor)
                          && ('constructor' in a && 'constructor' in b)) {
        return false;
      }
    }
    // Assume equality for cyclic structures. The algorithm for detecting cyclic
    // structures is adapted from ES 5.1 section 15.12.3, abstract operation `JO`.

    // Initializing stack of traversed objects.
    // It's done here since we only need them for objects and arrays comparison.
    aStack = aStack || [];
    bStack = bStack || [];
    var length = aStack.length;
    while (length--) {
      // Linear search. Performance is inversely proportional to the number of
      // unique nested structures.
      if (aStack[length] === a) return bStack[length] === b;
    }

    // Add the first object to the stack of traversed objects.
    aStack.push(a);
    bStack.push(b);

    // Recursively compare objects and arrays.
    if (areArrays) {
      // Compare array lengths to determine if a deep comparison is necessary.
      length = a.length;
      if (length !== b.length) return false;
      // Deep compare the contents, ignoring non-numeric properties.
      while (length--) {
        if (!eq(a[length], b[length], aStack, bStack)) return false;
      }
    } else {
      // Deep compare objects.
      var keys = _.keys(a), key;
      length = keys.length;
      // Ensure that both objects contain the same number of properties before comparing deep equality.
      if (_.keys(b).length !== length) return false;
      while (length--) {
        // Deep compare each member
        key = keys[length];
        if (!(has(b, key) && eq(a[key], b[key], aStack, bStack))) return false;
      }
    }
    // Remove the first object from the stack of traversed objects.
    aStack.pop();
    bStack.pop();
    return true;
  };

  // Perform a deep comparison to check if two objects are equal.
  _.isEqual = function(a, b) {
    return eq(a, b);
  };

  // Is a given array, string, or object empty?
  // An "empty" object has no enumerable own-properties.
  _.isEmpty = function(obj) {
    if (obj == null) return true;
    if (isArrayLike(obj) && (_.isArray(obj) || _.isString(obj) || _.isArguments(obj))) return obj.length === 0;
    return _.keys(obj).length === 0;
  };

  // Is a given value a DOM element?
  _.isElement = function(obj) {
    return !!(obj && obj.nodeType === 1);
  };

  // Is a given value an array?
  // Delegates to ECMA5's native Array.isArray
  _.isArray = nativeIsArray || function(obj) {
    return toString.call(obj) === '[object Array]';
  };

  // Is a given variable an object?
  _.isObject = function(obj) {
    var type = typeof obj;
    return type === 'function' || type === 'object' && !!obj;
  };

  // Add some isType methods: isArguments, isFunction, isString, isNumber, isDate, isRegExp, isError, isMap, isWeakMap, isSet, isWeakSet.
  _.each(['Arguments', 'Function', 'String', 'Number', 'Date', 'RegExp', 'Error', 'Symbol', 'Map', 'WeakMap', 'Set', 'WeakSet'], function(name) {
    _['is' + name] = function(obj) {
      return toString.call(obj) === '[object ' + name + ']';
    };
  });

  // Define a fallback version of the method in browsers (ahem, IE < 9), where
  // there isn't any inspectable "Arguments" type.
  if (!_.isArguments(arguments)) {
    _.isArguments = function(obj) {
      return has(obj, 'callee');
    };
  }

  // Optimize `isFunction` if appropriate. Work around some typeof bugs in old v8,
  // IE 11 (#1621), Safari 8 (#1929), and PhantomJS (#2236).
  var nodelist = root.document && root.document.childNodes;
  if (typeof /./ != 'function' && typeof Int8Array != 'object' && typeof nodelist != 'function') {
    _.isFunction = function(obj) {
      return typeof obj == 'function' || false;
    };
  }

  // Is a given object a finite number?
  _.isFinite = function(obj) {
    return !_.isSymbol(obj) && isFinite(obj) && !isNaN(parseFloat(obj));
  };

  // Is the given value `NaN`?
  _.isNaN = function(obj) {
    return _.isNumber(obj) && isNaN(obj);
  };

  // Is a given value a boolean?
  _.isBoolean = function(obj) {
    return obj === true || obj === false || toString.call(obj) === '[object Boolean]';
  };

  // Is a given value equal to null?
  _.isNull = function(obj) {
    return obj === null;
  };

  // Is a given variable undefined?
  _.isUndefined = function(obj) {
    return obj === void 0;
  };

  // Shortcut function for checking if an object has a given property directly
  // on itself (in other words, not on a prototype).
  _.has = function(obj, path) {
    if (!_.isArray(path)) {
      return has(obj, path);
    }
    var length = path.length;
    for (var i = 0; i < length; i++) {
      var key = path[i];
      if (obj == null || !hasOwnProperty.call(obj, key)) {
        return false;
      }
      obj = obj[key];
    }
    return !!length;
  };

  // Utility Functions
  // -----------------

  // Run Underscore.js in *noConflict* mode, returning the `_` variable to its
  // previous owner. Returns a reference to the Underscore object.
  _.noConflict = function() {
    root._ = previousUnderscore;
    return this;
  };

  // Keep the identity function around for default iteratees.
  _.identity = function(value) {
    return value;
  };

  // Predicate-generating functions. Often useful outside of Underscore.
  _.constant = function(value) {
    return function() {
      return value;
    };
  };

  _.noop = function(){};

  // Creates a function that, when passed an object, will traverse that object’s
  // properties down the given `path`, specified as an array of keys or indexes.
  _.property = function(path) {
    if (!_.isArray(path)) {
      return shallowProperty(path);
    }
    return function(obj) {
      return deepGet(obj, path);
    };
  };

  // Generates a function for a given object that returns a given property.
  _.propertyOf = function(obj) {
    if (obj == null) {
      return function(){};
    }
    return function(path) {
      return !_.isArray(path) ? obj[path] : deepGet(obj, path);
    };
  };

  // Returns a predicate for checking whether an object has a given set of
  // `key:value` pairs.
  _.matcher = _.matches = function(attrs) {
    attrs = _.extendOwn({}, attrs);
    return function(obj) {
      return _.isMatch(obj, attrs);
    };
  };

  // Run a function **n** times.
  _.times = function(n, iteratee, context) {
    var accum = Array(Math.max(0, n));
    iteratee = optimizeCb(iteratee, context, 1);
    for (var i = 0; i < n; i++) accum[i] = iteratee(i);
    return accum;
  };

  // Return a random integer between min and max (inclusive).
  _.random = function(min, max) {
    if (max == null) {
      max = min;
      min = 0;
    }
    return min + Math.floor(Math.random() * (max - min + 1));
  };

  // A (possibly faster) way to get the current timestamp as an integer.
  _.now = Date.now || function() {
    return new Date().getTime();
  };

  // List of HTML entities for escaping.
  var escapeMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#x27;',
    '`': '&#x60;'
  };
  var unescapeMap = _.invert(escapeMap);

  // Functions for escaping and unescaping strings to/from HTML interpolation.
  var createEscaper = function(map) {
    var escaper = function(match) {
      return map[match];
    };
    // Regexes for identifying a key that needs to be escaped.
    var source = '(?:' + _.keys(map).join('|') + ')';
    var testRegexp = RegExp(source);
    var replaceRegexp = RegExp(source, 'g');
    return function(string) {
      string = string == null ? '' : '' + string;
      return testRegexp.test(string) ? string.replace(replaceRegexp, escaper) : string;
    };
  };
  _.escape = createEscaper(escapeMap);
  _.unescape = createEscaper(unescapeMap);

  // Traverses the children of `obj` along `path`. If a child is a function, it
  // is invoked with its parent as context. Returns the value of the final
  // child, or `fallback` if any child is undefined.
  _.result = function(obj, path, fallback) {
    if (!_.isArray(path)) path = [path];
    var length = path.length;
    if (!length) {
      return _.isFunction(fallback) ? fallback.call(obj) : fallback;
    }
    for (var i = 0; i < length; i++) {
      var prop = obj == null ? void 0 : obj[path[i]];
      if (prop === void 0) {
        prop = fallback;
        i = length; // Ensure we don't continue iterating.
      }
      obj = _.isFunction(prop) ? prop.call(obj) : prop;
    }
    return obj;
  };

  // Generate a unique integer id (unique within the entire client session).
  // Useful for temporary DOM ids.
  var idCounter = 0;
  _.uniqueId = function(prefix) {
    var id = ++idCounter + '';
    return prefix ? prefix + id : id;
  };

  // By default, Underscore uses ERB-style template delimiters, change the
  // following template settings to use alternative delimiters.
  _.templateSettings = {
    evaluate: /<%([\s\S]+?)%>/g,
    interpolate: /<%=([\s\S]+?)%>/g,
    escape: /<%-([\s\S]+?)%>/g
  };

  // When customizing `templateSettings`, if you don't want to define an
  // interpolation, evaluation or escaping regex, we need one that is
  // guaranteed not to match.
  var noMatch = /(.)^/;

  // Certain characters need to be escaped so that they can be put into a
  // string literal.
  var escapes = {
    "'": "'",
    '\\': '\\',
    '\r': 'r',
    '\n': 'n',
    '\u2028': 'u2028',
    '\u2029': 'u2029'
  };

  var escapeRegExp = /\\|'|\r|\n|\u2028|\u2029/g;

  var escapeChar = function(match) {
    return '\\' + escapes[match];
  };

  // JavaScript micro-templating, similar to John Resig's implementation.
  // Underscore templating handles arbitrary delimiters, preserves whitespace,
  // and correctly escapes quotes within interpolated code.
  // NB: `oldSettings` only exists for backwards compatibility.
  _.template = function(text, settings, oldSettings) {
    if (!settings && oldSettings) settings = oldSettings;
    settings = _.defaults({}, settings, _.templateSettings);

    // Combine delimiters into one regular expression via alternation.
    var matcher = RegExp([
      (settings.escape || noMatch).source,
      (settings.interpolate || noMatch).source,
      (settings.evaluate || noMatch).source
    ].join('|') + '|$', 'g');

    // Compile the template source, escaping string literals appropriately.
    var index = 0;
    var source = "__p+='";
    text.replace(matcher, function(match, escape, interpolate, evaluate, offset) {
      source += text.slice(index, offset).replace(escapeRegExp, escapeChar);
      index = offset + match.length;

      if (escape) {
        source += "'+\n((__t=(" + escape + "))==null?'':_.escape(__t))+\n'";
      } else if (interpolate) {
        source += "'+\n((__t=(" + interpolate + "))==null?'':__t)+\n'";
      } else if (evaluate) {
        source += "';\n" + evaluate + "\n__p+='";
      }

      // Adobe VMs need the match returned to produce the correct offset.
      return match;
    });
    source += "';\n";

    // If a variable is not specified, place data values in local scope.
    if (!settings.variable) source = 'with(obj||{}){\n' + source + '}\n';

    source = "var __t,__p='',__j=Array.prototype.join," +
      "print=function(){__p+=__j.call(arguments,'');};\n" +
      source + 'return __p;\n';

    var render;
    try {
      render = new Function(settings.variable || 'obj', '_', source);
    } catch (e) {
      e.source = source;
      throw e;
    }

    var template = function(data) {
      return render.call(this, data, _);
    };

    // Provide the compiled source as a convenience for precompilation.
    var argument = settings.variable || 'obj';
    template.source = 'function(' + argument + '){\n' + source + '}';

    return template;
  };

  // Add a "chain" function. Start chaining a wrapped Underscore object.
  _.chain = function(obj) {
    var instance = _(obj);
    instance._chain = true;
    return instance;
  };

  // OOP
  // ---------------
  // If Underscore is called as a function, it returns a wrapped object that
  // can be used OO-style. This wrapper holds altered versions of all the
  // underscore functions. Wrapped objects may be chained.

  // Helper function to continue chaining intermediate results.
  var chainResult = function(instance, obj) {
    return instance._chain ? _(obj).chain() : obj;
  };

  // Add your own custom functions to the Underscore object.
  _.mixin = function(obj) {
    _.each(_.functions(obj), function(name) {
      var func = _[name] = obj[name];
      _.prototype[name] = function() {
        var args = [this._wrapped];
        push.apply(args, arguments);
        return chainResult(this, func.apply(_, args));
      };
    });
    return _;
  };

  // Add all of the Underscore functions to the wrapper object.
  _.mixin(_);

  // Add all mutator Array functions to the wrapper.
  _.each(['pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      var obj = this._wrapped;
      method.apply(obj, arguments);
      if ((name === 'shift' || name === 'splice') && obj.length === 0) delete obj[0];
      return chainResult(this, obj);
    };
  });

  // Add all accessor Array functions to the wrapper.
  _.each(['concat', 'join', 'slice'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      return chainResult(this, method.apply(this._wrapped, arguments));
    };
  });

  // Extracts the result from a wrapped and chained object.
  _.prototype.value = function() {
    return this._wrapped;
  };

  // Provide unwrapping proxy for some methods used in engine operations
  // such as arithmetic and JSON stringification.
  _.prototype.valueOf = _.prototype.toJSON = _.prototype.value;

  _.prototype.toString = function() {
    return String(this._wrapped);
  };

  // AMD registration happens at the end for compatibility with AMD loaders
  // that may not enforce next-turn semantics on modules. Even though general
  // practice for AMD registration is to be anonymous, underscore registers
  // as a named module because, like jQuery, it is a base library that is
  // popular enough to be bundled in a third party lib, but not be part of
  // an AMD load request. Those cases could generate an error when an
  // anonymous define() is called outside of a loader request.
  if (typeof define == 'function' && define.amd) {
    define('underscore', [], function() {
      return _;
    });
  }
}());/*global jQuery */
/*!
* FitText.js 1.2
*
* Copyright 2011, Dave Rupert http://daverupert.com
* Released under the WTFPL license
* http://sam.zoy.org/wtfpl/
*
* Date: Thu May 05 14:23:00 2011 -0600
*/

(function( $ ){

  $.fn.fitText = function( kompressor, options ) {

    // Setup options
    var compressor = kompressor || 1,
        settings = $.extend({
          'minFontSize' : Number.NEGATIVE_INFINITY,
          'maxFontSize' : Number.POSITIVE_INFINITY
        }, options);

    return this.each(function(){

      // Store the object
      var $this = $(this);

      // Resizer() resizes items based on the object width divided by the compressor * 10
      var resizer = function () {
        $this.css('font-size', Math.max(Math.min($this.width() / (compressor*10), parseFloat(settings.maxFontSize)), parseFloat(settings.minFontSize)));
      };

      // Call once to set.
      resizer();

      // Call on resize. Opera debounces their resize by default.
      $(window).on('resize.fittext orientationchange.fittext', resizer);

    });
  };

})( jQuery );/* ===================================================
 * jquerynimbleLazyLoad.js v1.0.0
 * ===================================================
 *
 * Replace all img src placeholder in the $element by the real src on scroll window event
 * Bind a 'smartload' event on each transformed img
 *
 * Note : the data-src (data-srcset) attr has to be pre-processed before the actual page load
 * Example of regex to pre-process img server side with php :
 * preg_replace_callback('#<img([^>]+?)src=[\'"]?([^\'"\s>]+)[\'"]?([^>]*)>#', 'regex_callback' , $_html)
 *
 * (c) 2018 Nicolas Guillaume, Nice, France
 *
 * Example of gif 1px x 1px placeholder :
 * 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
 *
 * inspired by the work of Luís Almeida
 * http://luis-almeida.github.com/unveil
 *
 * Requires requestAnimationFrame polyfill:
 * http://paulirish.com/2011/requestanimationframe-for-smart-animating/
 *
 * Feb 2019 : added support for iframe lazyloading for https://github.com/presscustomizr/nimble-builder/issues/361
 * =================================================== */
(function ( $, window ) {
      //defaults
      var pluginName = 'nimbleLazyLoad',
          defaults = {
                load_all_images_on_first_scroll : false,
                //attribute : [ 'data-sek-src' ],
                excludeImg : [],
                threshold : 200,
                fadeIn_options : { duration : 400 },
                delaySmartLoadEvent : 0,

          },
          //with intersecting cointainers:
          //- to avoid race conditions
          //- to avoid multi processing in general
          skipLazyLoadClass = 'smartload-skip';


      function Plugin( element, options ) {
            this.element = element;
            this.options = $.extend( {}, defaults, options) ;
            //add .smartload-skip to the excludeImg
            if ( _utils_.isArray( this.options.excludeImg ) ) {
                  this.options.excludeImg.push( '.'+skipLazyLoadClass );
            } else {
                  this.options.excludeImg = [ '.'+skipLazyLoadClass ];
            }

            this._defaults = defaults;
            this._name = pluginName;
            this.init();
      }


      //can access this.element and this.option
      Plugin.prototype.init = function () {
            var self        = this,
                $_ImgOrDivOrIFrameElements  = $( '[data-sek-src]:not('+ this.options.excludeImg.join() +'), [data-sek-iframe-src]' , this.element );

            this.increment  = 1;//used to wait a little bit after the first user scroll actions to trigger the timer
            this.timer      = 0;

            $_ImgOrDivOrIFrameElements
                  //avoid intersecting containers to parse the same images
                  .addClass( skipLazyLoadClass )
                  .bind( 'sek_load_img', {}, function() { self._load_img(this); })
                  .bind( 'sek_load_iframe', {}, function() { self._load_iframe(this); });

            //the scroll event gets throttled with the requestAnimationFrame
            $(window).scroll( function( _evt ) {
                  self._better_scroll_event_handler( $_ImgOrDivOrIFrameElements, _evt );
            });
            //debounced resize event
            $(window).resize( _utils_.debounce( function( _evt ) {
                  self._maybe_trigger_load( $_ImgOrDivOrIFrameElements, _evt );
            }, 100 ) );
            //on load
            this._maybe_trigger_load( $_ImgOrDivOrIFrameElements);

      };


      /*
      * @param : array of $img
      * @param : current event
      * @return : void
      * scroll event performance enhancer => avoid browser stack if too much scrolls
      */
      Plugin.prototype._better_scroll_event_handler = function( $_Elements , _evt ) {
            var self = this;
            if ( ! this.doingAnimation ) {
                  this.doingAnimation = true;
                  window.requestAnimationFrame(function() {
                        self._maybe_trigger_load( $_Elements , _evt );
                        self.doingAnimation = false;
                  });
            }
      };


      /*
      * @param : array of $img
      * @param : current event
      * @return : void
      */
      Plugin.prototype._maybe_trigger_load = function( $_Elements , _evt ) {
            var self = this,
                //get the visible images list
                _visible_list = $_Elements.filter( function( ind, _el ) { return self._is_visible( _el ,  _evt ); } );

            _visible_list.map( function( ind, _el ) {
                  if ( 'IFRAME' === $(_el).prop("tagName") ) {
                        $(_el).trigger( 'sek_load_iframe' );
                  } else {
                        $(_el).trigger( 'sek_load_img' );
                  }
            });
      };


      /*
      * @param single $img object
      * @param : current event
      * @return bool
      * helper to check if an image is the visible ( viewport + custom option threshold)
      */
      Plugin.prototype._is_visible = function( element, _evt ) {
            var sniffFirstVisiblePrevElement = function( $el ) {
                  if ( $el.length > 0 && $el.is(':visible') )
                    return $el;
                  var $prev = $el.prev();
                  // if there's a previous sibling and this sibling is visible, use it
                  if ( $prev.length > 0 && $prev.is(':visible') ) {
                      return $prev;
                  }
                  // if there's a previous sibling but it's not visible, let's try the next previous sibling
                  if ( $prev.length > 0 && !$prev.is(':visible') ) {
                      return sniffFirstVisiblePrevElement( $prev );
                  }
                  // if no previous sibling visible, let's go up the parent level
                  var $parent = $el.parent();
                  if ( $parent.length > 0 ) {
                      return sniffFirstVisiblePrevElement( $parent );
                  }
                  // we don't have siblings or parent
                  return null;
            };

            // Is the candidate visible ? <= not display:none
            // If not visible, we can't determine the offset().top because of https://github.com/presscustomizr/nimble-builder/issues/363
            // So let's sniff up in the DOM to find the first visible sibling or container
            var $el_candidate = sniffFirstVisiblePrevElement( $(element) );
            if ( !$el_candidate || $el_candidate.length < 1 )
              return false;

            var wt = $(window).scrollTop(),
                wb = wt + $(window).height(),
                it  = $el_candidate.offset().top,
                ib  = it + $el_candidate.height(),
                th = this.options.threshold;

            //force all images to visible if first scroll option enabled
            if ( _evt && 'scroll' == _evt.type && this.options.load_all_images_on_first_scroll )
              return true;

            return ib >= wt - th && it <= wb + th;
      };


      /*
      * @param single $img object
      * @return void
      * replace src place holder by data-src attr val which should include the real src
      */
      Plugin.prototype._load_img = function( _el_ ) {
            var $_el    = $(_el_),
                _src     = $_el.attr( 'data-sek-src' ),
                _src_set = $_el.attr( 'data-sek-srcset' ),
                _sizes   = $_el.attr( 'data-sek-sizes' ),
                self = this,
                $jQueryImgToLoad = $("<img />", { src : _src } );

            $_el.addClass('lazy-loading');
            $_el.unbind('sek_load_img');

            $jQueryImgToLoad
                  // .hide()
                  .load( function () {
                        //https://api.jquery.com/removeAttr/
                        //An attribute to remove; as of version 1.7, it can be a space-separated list of attributes.
                        //minimum supported wp version (3.4+) embeds jQuery 1.7.2
                        $_el.removeAttr( [ 'data-sek-src', 'data-sek-srcset', 'data-sek-sizes' ].join(' ') );
                        if( $_el.data("sek-lazy-bg") ){
                              $_el.css('backgroundImage', 'url('+_src+')');
                        } else {
                              $_el.attr("src", _src );
                              if ( _src_set ) {
                                    $_el.attr("srcset", _src_set );
                              }
                              if ( _sizes ) {
                                    $_el.attr("sizes", _sizes );
                              }
                        }
                        //prevent executing this twice on an already smartloaded img
                        if ( ! $_el.hasClass('sek-lazy-loaded') ) {
                              $_el.addClass('sek-lazy-loaded');
                        }
                        //Following would be executed twice if needed, as some browsers at the
                        //first execution of the load callback might still have not actually loaded the img

                        $_el.trigger('smartload');
                        //flag to avoid double triggering
                        $_el.data('sek-lazy-loaded', true );
                  });//<= create a load() fn
            //http://stackoverflow.com/questions/1948672/how-to-tell-if-an-image-is-loaded-or-cached-in-jquery
            if ( $jQueryImgToLoad[0].complete ) {
                  $jQueryImgToLoad.load();
            }
            $_el.removeClass('lazy-loading');
      };


      /*
      * @param single iframe el object
      * @return void
      */
      Plugin.prototype._load_iframe = function( _el_ ) {
            var $_el    = $(_el_),
                self = this;

            //$_el.addClass('lazy-loading');
            $_el.unbind('sek_load_iframe');

            $_el.attr( 'src', function() {
                  var src = $(this).attr('data-sek-iframe-src');
                  $(this).removeAttr('data-sek-iframe-src');
                  $_el.data('sek-lazy-loaded', true );
                  $_el.trigger('smartload');
                  if ( ! $_el.hasClass('sek-lazy-loaded') ) {
                        $_el.addClass('sek-lazy-loaded');
                  }
                  return src;
            });
            //$_el.removeClass('lazy-loading');
      };


      // prevents against multiple instantiations
      $.fn[pluginName] = function ( options ) {
            return this.each(function () {
                  if (!$.data(this, 'plugin_' + pluginName)) {
                        $.data(this, 'plugin_' + pluginName,
                        new Plugin( this, options ));
                  }
            });
      };
})( jQuery, window );/* ===================================================
 * jquerynimbleCenterImages.js v1.0.0
 * ( inspired by Customizr theme jQuery plugin )
 * ===================================================
 * (c) 2019 Nicolas Guillaume, Nice, France
 * CenterImages plugin may be freely distributed under the terms of the GNU GPL v2.0 or later license.
 *
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Center images in a specified container
 *
 * =================================================== */
(function ( $, window ) {
      //defaults
      var pluginName = 'nimbleCenterImages',
          defaults = {
                enableCentering : true,
                onresize : true,
                onInit : true,//<= shall we smartload on init or wait for a custom event, typically smartload ?
                oncustom : [],//list of event here
                $containerToListen : null,//<= we might want to listen to custom event trigger to a parent container.Should be a jQuery obj
                imgSel : 'img',
                defaultCSSVal : { width : 'auto' , height : 'auto' },
                leftAdjust : 0,
                zeroLeftAdjust : 0,
                topAdjust : 0,
                zeroTopAdjust : -2,//<= top ajustement for sek-h-centrd
                useImgAttr:false,//uses the img height and width attributes if not visible (typically used for the customizr slider hidden images)
                setOpacityWhenCentered : false,//this can be used to hide the image during the time it is centered
                addCenteredClassWithDelay : 0,//<= a small delay can be required when we rely on the sek-v-centrd or sek-h-centrd css classes to set the opacity for example
                opacity : 1
          };

      function Plugin( element, options ) {
            var self = this;
            this.container  = element;
            this.options    = $.extend( {}, defaults, options) ;
            this._defaults  = defaults;
            this._name      = pluginName;
            this._customEvt = $.isArray(self.options.oncustom) ? self.options.oncustom : self.options.oncustom.split(' ');
            this.init();
      }

      //can access this.element and this.option
      //@return void
      Plugin.prototype.init = function () {
            var self = this,
                _do = function( _event_ ) {
                    _event_ = _event_ || 'init';

                    //parses imgs ( if any ) in current container
                    var $_imgs = $( self.options.imgSel , self.container );

                    //if no images or centering is not active, only handle the golden ratio on resize event
                    if ( 1 <= $_imgs.length && self.options.enableCentering ) {
                          self._parse_imgs( $_imgs, _event_ );
                    }
                };

            //fire
            if ( self.options.onInit ) {
                  _do();
            }

            //bind the container element with custom events if any
            //( the images will also be bound )
            if ( $.isArray( self._customEvt ) ) {
                  self._customEvt.map( function( evt ) {
                        var $_containerToListen = ( self.options.$containerToListen instanceof $ && 1 < self.options.$containerToListen.length ) ? self.options.$containerToListen : $( self.container );
                        $_containerToListen.bind( evt, {} , function() {
                              _do( evt );
                        });
                  } );
            }
      };


      //@return void
      Plugin.prototype._parse_imgs = function( $_imgs, _event_ ) {
            var self = this;
            $_imgs.each(function ( ind, img ) {
                  var $_img = $(img);
                  self._pre_img_cent( $_img, _event_ );

                  // IMG CENTERING FN ON RESIZE ?
                  // Parse Img can be fired several times, so bind once
                  if ( self.options.onresize && ! $_img.data('resize-react-bound' ) ) {
                        $_img.data('resize-react-bound', true );
                        $(window).resize( _utils_.debounce( function() {
                              self._pre_img_cent( $_img, 'resize');
                        }, 100 ) );
                  }

            });//$_imgs.each()

            // Mainly designed to check if a container is not getting parsed too many times
            if ( $(self.container).attr('data-img-centered-in-container') ) {
                  var _n = parseInt( $(self.container).attr('data-img-centered-in-container'), 10 ) + 1;
                  $(self.container).attr('data-img-centered-in-container', _n );
            } else {
                  $(self.container).attr('data-img-centered-in-container', 1 );
            }
      };



      //@return void
      Plugin.prototype._pre_img_cent = function( $_img ) {

            var _state = this._get_current_state( $_img ),
                self = this,
                _case  = _state.current,
                _p     = _state.prop[_case],
                _not_p = _state.prop[ 'h' == _case ? 'v' : 'h'],
                _not_p_dir_val = 'h' == _case ? ( this.options.zeroTopAdjust || 0 ) : ( this.options.zeroLeftAdjust || 0 );

            var _centerImg = function( $_img ) {
                  $_img
                      .css( _p.dim.name , _p.dim.val )
                      .css( _not_p.dim.name , self.options.defaultCSSVal[ _not_p.dim.name ] || 'auto' )
                      .css( _p.dir.name, _p.dir.val ).css( _not_p.dir.name, _not_p_dir_val );

                  if ( 0 !== self.options.addCenteredClassWithDelay && _utils_.isNumber( self.options.addCenteredClassWithDelay ) ) {
                        _utils_.delay( function() {
                              $_img.addClass( _p._class ).removeClass( _not_p._class );
                        }, self.options.addCenteredClassWithDelay );
                  } else {
                        $_img.addClass( _p._class ).removeClass( _not_p._class );
                  }

                  // Mainly designed to check if a single image is not getting parsed too many times
                  if ( $_img.attr('data-img-centered') ) {
                        var _n = parseInt( $_img.attr('data-img-centered'), 10 ) + 1;
                        $_img.attr('data-img-centered', _n );
                  } else {
                        $_img.attr('data-img-centered', 1 );
                  }
                  return $_img;
            };
            if ( this.options.setOpacityWhenCentered ) {
                  $.when( _centerImg( $_img ) ).done( function( $_img ) {
                        $_img.css( 'opacity', self.options.opacity );
                  });
            } else {
                  _utils_.delay(function() { _centerImg( $_img ); }, 0 );
            }
      };




      /********
      * HELPERS
      *********/
      //@return object with initial conditions : { current : 'h' or 'v', prop : {} }
      Plugin.prototype._get_current_state = function( $_img ) {
            var c_x     = $_img.closest(this.container).outerWidth(),
                c_y     = $(this.container).outerHeight(),
                i_x     = this._get_img_dim( $_img , 'x'),
                i_y     = this._get_img_dim( $_img , 'y'),
                up_i_x  = i_y * c_y !== 0 ? Math.round( i_x / i_y * c_y ) : c_x,
                up_i_y  = i_x * c_x !== 0 ? Math.round( i_y / i_x * c_x ) : c_y,
                current = 'h';
            //avoid dividing by zero if c_x or i_x === 0
            if ( 0 !== c_x * i_x ) {
                  current = ( c_y / c_x ) >= ( i_y / i_x ) ? 'h' : 'v';
            }

            var prop    = {
                  h : {
                        dim : { name : 'height', val : c_y },
                        dir : { name : 'left', val : ( c_x - up_i_x ) / 2 + ( this.options.leftAdjust || 0 ) },
                        _class : 'sek-h-centrd'
                  },
                  v : {
                        dim : { name : 'width', val : c_x },
                        dir : { name : 'top', val : ( c_y - up_i_y ) / 2 + ( this.options.topAdjust || 0 ) },
                        _class : 'sek-v-centrd'
                  }
            };

            return { current : current , prop : prop };
      };

      //@return img height or width
      //uses the img height and width if not visible and set in options
      Plugin.prototype._get_img_dim = function( $_img, _dim ) {
            if ( ! this.options.useImgAttr )
              return 'x' == _dim ? $_img.outerWidth() : $_img.outerHeight();

            if ( $_img.is(":visible") ) {
                  return 'x' == _dim ? $_img.outerWidth() : $_img.outerHeight();
            } else {
                  if ( 'x' == _dim ){
                        var _width = $_img.originalWidth();
                        return typeof _width === undefined ? 0 : _width;
                  }
                  if ( 'y' == _dim ){
                        var _height = $_img.originalHeight();
                        return typeof _height === undefined ? 0 : _height;
                  }
            }
      };

      /*
      * @params string : ids or classes
      * @return boolean
      */
      Plugin.prototype._is_selector_allowed = function() {
            //has requested sel ?
            if ( ! $(this.container).attr( 'class' ) )
              return true;

            var _elSels       = $(this.container).attr( 'class' ).split(' '),
                _selsToSkip   = [],
                _filtered     = _elSels.filter( function(classe) { return -1 != $.inArray( classe , _selsToSkip ) ;});

            //check if the filtered selectors array with the non authorized selectors is empty or not
            //if empty => all selectors are allowed
            //if not, at least one is not allowed
            return 0 === _filtered.length;
      };


      // prevents against multiple instantiations
      $.fn[pluginName] = function ( options ) {
            return this.each(function () {
                if (!$.data(this, 'plugin_' + pluginName)) {
                    $.data(this, 'plugin_' + pluginName,
                    new Plugin( this, options ));
                }
            });
      };

})( jQuery, window );// global sekFrontLocalized
/* ===================================================
 * jquery.fn.parallaxBg v1.0.0
 * Created in October 2018.
 * Inspired from https://github.com/presscustomizr/front-jquery-plugins/blob/master/jqueryParallax.js
 * ===================================================
*/
(function ( $, window ) {
      //defaults
      var pluginName = 'parallaxBg',
          defaults = {
                parallaxForce : 40,
                oncustom : [],//list of event here
                matchMedia : 'only screen and (max-width: 800px)'
          };

      function Plugin( element, options ) {
            this.element         = $(element);
            //this.element_wrapper = this.element.closest( '.parallax-wrapper' );
            this.options         = $.extend( {}, defaults, options, this.parseElementDataOptions() ) ;
            this._defaults       = defaults;
            this._name           = pluginName;
            this.init();
      }

      Plugin.prototype.parseElementDataOptions = function () {
            return this.element.data();
      };

      //can access this.element and this.option
      //@return void
      Plugin.prototype.init = function () {
            var self = this;
            //cache some element
            this.$_window     = $(window);
            this.doingAnimation = false;
            this.isVisible = false;
            this.isBefore = false;//the element is before the scroll point
            this.isAfter = true;// the element is after the scroll point

            // normalize the parallax ratio
            // must be a number 0 > ratio > 100
            if ( 'number' !== typeof( self.options.parallaxForce ) || self.options.parallaxForce < 0 ) {
                  if ( sekFrontLocalized.isDevMode ) {
                        console.log('parallaxBg => the provided parallaxForce is invalid => ' + self.options.parallaxForce );
                  }
                  self.options.parallaxForce = this._defaults.parallaxForce;
            }
            if ( self.options.parallaxForce > 100 ) {
                  self.options.parallaxForce = 100;
            }

            //the scroll event gets throttled with the requestAnimationFrame
            this.$_window.scroll( function(_evt) { self.maybeParallaxMe(_evt); } );
            //debounced resize event
            this.$_window.resize( _utils_.debounce( function(_evt) {
                  self.maybeParallaxMe(_evt);
            }, 100 ) );

            //on load
            this.checkIfIsVisibleAndCacheProperties();
            this.setTopPositionAndBackgroundSize();
      };

      //@see https://www.paulirish.com/2012/why-moving-elements-with-translate-is-better-than-posabs-topleft/
      Plugin.prototype.setTopPositionAndBackgroundSize = function() {
            var self = this;

            // options.matchMedia is set to 'only screen and (max-width: 768px)' by default
            // if a match is found, then reset the top position
            if ( _utils_.isFunction( window.matchMedia ) && matchMedia( self.options.matchMedia ).matches ) {
                  this.element.css({'background-position-y' : '', 'background-attachment' : '' });
                  return;
            }

            var $element       = this.element,
                elemHeight = $element.outerHeight(),
                winHeight = this.$_window.height(),
                offsetTop = $element.offset().top,
                scrollTop = this.$_window.scrollTop(),
                percentOfPage = 100;

            // the percentOfPage can vary from -1 to 1
            if ( this.isVisible ) {
                  //percentOfPage = currentDistanceToMiddleScreen / maxDistanceToMiddleScreen;
                  percentOfPage = ( offsetTop - scrollTop ) / winHeight;
            } else if ( this.isBefore ) {
                  percentOfPage = 1;
            } else if ( this.isAfter ) {
                  percentOfPage = - 1;
            }

            var maxBGYMove = this.options.parallaxForce > 0 ? winHeight * ( 100 - this.options.parallaxForce ) / 100 : winHeight,
                bgPositionY = Math.round( percentOfPage *  maxBGYMove );

            this.element.css({
                  'background-position-y' : [
                        'calc(50% ',
                        bgPositionY > 0 ? '+ ' : '- ',
                        Math.abs( bgPositionY ) + 'px)'
                  ].join('')
            });
      };

      // When does the image enter the viewport ?
      Plugin.prototype.checkIfIsVisibleAndCacheProperties = function( _evt ) {
          var $element = this.element;
          // bail if the level is display:none;
          // because $.offset() won't work
          // see because of https://github.com/presscustomizr/nimble-builder/issues/363
          if ( ! $element.is(':visible') )
              return false;

          var scrollTop = this.$_window.scrollTop(),
              wb = scrollTop + this.$_window.height(),
              offsetTop  = $element.offset().top,
              ib  = offsetTop + $element.outerHeight();

          // Cache now
          this.isVisible = ib >= scrollTop && offsetTop <= wb;
          this.isBefore = offsetTop > wb ;//the element is before the scroll point
          this.isAfter = ib < scrollTop;// the element is after the scroll point
          return this.isVisible;
      };

      // a throttle is implemented with window.requestAnimationFrame
      Plugin.prototype.maybeParallaxMe = function(evt) {
            var self = this;
            if ( ! this.checkIfIsVisibleAndCacheProperties() )
              return;

            if ( ! this.doingAnimation ) {
                  this.doingAnimation = true;
                  window.requestAnimationFrame(function() {
                        self.setTopPositionAndBackgroundSize();
                        self.doingAnimation = false;
                  });
            }
      };

      // prevents against multiple instantiations
      $.fn[pluginName] = function ( options ) {
          return this.each(function () {
              if (!$.data(this, 'plugin_' + pluginName)) {
                  $.data(this, 'plugin_' + pluginName,
                  new Plugin( this, options ));
              }
          });
      };
})( jQuery, window );// global sekFrontLocalized
/* ------------------------------------------------------------------------- *
 *  MENU
/* ------------------------------------------------------------------------- */
jQuery( function($){
    //DROPDOWN
    var Dropdown = function() {
          //dropdown
          var DATA_KEY  = 'sek.sekDropdown',
              EVENT_KEY = '.' + DATA_KEY,
              Event     = {
                PLACE_ME  : 'placeme'+ EVENT_KEY,
                PLACE_ALL : 'placeall' + EVENT_KEY,
                SHOWN     : 'shown' + EVENT_KEY,
                SHOW      : 'show' + EVENT_KEY,
                HIDDEN    : 'hidden' + EVENT_KEY,
                HIDE      : 'hide' + EVENT_KEY,
                CLICK     : 'click' + EVENT_KEY,
                TAP       : 'tap' + EVENT_KEY,
              },
              ClassName = {
                DROPDOWN                : 'sek-dropdown-menu',
                DROPDOWN_SUBMENU        : 'sek-dropdown-submenu',
                SHOW                    : 'show',
                PARENTS                 : 'menu-item-has-children',
              },
              Selector = {
                DATA_TOGGLE              : '[data-toggle="sek-dropdown"]',
                DATA_SHOWN_TOGGLE_LINK   : '.' +ClassName.SHOW+ '> a',
                HOVER_MENU               : '.sek-nav-wrap',
                HOVER_PARENT             : '.sek-nav-wrap .menu-item-has-children',
                PARENTS                  : '.sek-nav-wrap .menu-item-has-children',
                SNAKE_PARENTS            : '.sek-nav-wrap .menu-item-has-children',
              };

          // unify all the dropdowns classes whether the menu is a proper menu or the all pages fall-back
          $( '.sek-nav .children, .sek-nav .sub-menu' ).addClass( ClassName.DROPDOWN );
          $( '.sek-nav-wrap .page_item_has_children' ).addClass( ClassName.PARENTS );
          $( '.sek-nav' + ' .' + ClassName.DROPDOWN + ' .' + ClassName.PARENTS ).addClass( ClassName.DROPDOWN_SUBMENU );

          //Handle dropdown on hover via js
          var dropdownMenuOnHover = function() {
                var _dropdown_selector = Selector.HOVER_PARENT;

                function _addOpenClass () {
                      var $_el = $(this);

                      //a little delay to balance the one added in removing the open class
                      var _debounced_addOpenClass = _utils_.debounce( function() {
                            //do nothing if menu is mobile
                            if( 'static' == $_el.find( '.'+ClassName.DROPDOWN ).css( 'position' ) ) {
                                  return false;
                            }
                            if ( ! $_el.hasClass(ClassName.SHOW) ) {
                                  $_el.trigger( Event.SHOW )
                                      .addClass(ClassName.SHOW)
                                      .trigger( Event.SHOWN);

                                  var $_data_toggle = $_el.children( Selector.DATA_TOGGLE );

                                  if ( $_data_toggle.length ) {
                                        $_data_toggle[0].setAttribute('aria-expanded', 'true');
                                  }
                            }
                      }, 30);

                      _debounced_addOpenClass();
                }

                function _removeOpenClass () {

                      var $_el = $(this);

                      //a little delay before closing to avoid closing a parent before accessing the child
                      var _debounced_removeOpenClass = _utils_.debounce( function() {
                            if ( $_el.find("ul li:hover").length < 1 && ! $_el.closest('ul').find('li:hover').is( $_el ) ) {
                                  $_el.trigger( Event.HIDE )
                                      .removeClass( ClassName.SHOW)
                                      .trigger( Event.HIDDEN );

                                  var $_data_toggle = $_el.children( Selector.DATA_TOGGLE );

                                  if ( $_data_toggle.length ) {
                                        $_data_toggle[0].setAttribute('aria-expanded', 'false');
                                  }
                            }
                      }, 30 );

                      _debounced_removeOpenClass();
                }

                //BIND
                $( document )
                    .on( 'mouseenter', _dropdown_selector, _addOpenClass )
                    .on( 'mouseleave', _dropdown_selector , _removeOpenClass );
          },

          //SNAKE
          dropdownPlacement = function() {
                var isRTL = 'rtl' === $('html').attr('dir'),
                    doingAnimation = false;

                $(window)
                    //on resize trigger Event.PLACE on active dropdowns
                    .on( 'resize', function() {
                            if ( ! doingAnimation ) {
                                  doingAnimation = true;
                                  window.requestAnimationFrame(function() {
                                    //trigger a placement on the open dropdowns
                                    $( Selector.SNAKE_PARENTS+'.'+ClassName.SHOW)
                                        .trigger(Event.PLACE_ME);
                                    doingAnimation = false;
                                  });
                            }

                    });

                $( document )
                    .on( Event.PLACE_ALL, function() {
                                //trigger a placement on all
                                $( Selector.SNAKE_PARENTS )
                                    .trigger(Event.PLACE_ME);
                    })
                    //snake bound on menu-item shown and place
                    .on( Event.SHOWN+' '+Event.PLACE_ME, Selector.SNAKE_PARENTS, function(evt) {
                      evt.stopPropagation();
                      _do_snake( $(this), evt );
                    });


                //snake
                //$_el is the menu item with children whose submenu will be 'snaked'
                function _do_snake( $_el, evt ) {
                      if ( !( evt && evt.namespace && DATA_KEY === evt.namespace ) ) {
                            return;
                      }

                      var $_this       = $_el,
                          $_dropdown   = $_this.children( '.'+ClassName.DROPDOWN );

                      if ( !$_dropdown.length ) {
                            return;
                      }

                      //stage
                      /*
                      * we display the dropdown so that jQuery is able to retrieve exact size and positioning
                      * we also hide whatever overflows the menu item with children whose submenu will be 'snaked'
                      * this to avoid some glitches that would made it lose the focus:
                      * During RTL testing when a menu item with children reached the left edge of the window
                      * it happened that while the submenu was showing (because of the show class added, so not depending on the snake)
                      * this submenu (ul) stole the focus and then released it in a very short time making the mouseleave callback
                      * defined in dropdownMenuOnHover react, hence closing the whole submenu tree.
                      * This might be a false positive, as we don't really test RTL with RTL browsers (only the html direction changes),
                      * but since the 'cure' has no side effects, let's be pedantic!
                      */
                      $_el.css( 'overflow', 'hidden' );
                      $_dropdown.css( {
                        'zIndex'  : '-100',
                        'display' : 'block'
                      });

                      _maybe_move( $_dropdown, $_el );

                      //unstage
                      $_dropdown.css({
                        'zIndex'  : '',
                        'display' : ''
                      });
                      $_el.css( 'overflow', '' );
                }//_so_snake


                function _maybe_move( $_dropdown, $_el ) {
                      var Direction          = isRTL ? {
                                //when in RTL we open the submenu by default on the left side
                                _DEFAULT          : 'left',
                                _OPPOSITE         : 'right'
                          } : {
                                //when in LTR we open the submenu by default on the right side
                                _DEFAULT          : 'right',
                                _OPPOSITE         : 'left'
                          },
                          ClassName          = {
                                OPEN_PREFIX       : 'open-',
                                DD_SUBMENU        : 'sek-dropdown-submenu',
                                CARET_TITLE_FLIP  : 'sek-menu-link__row-reverse',
                                //CARET             : 'caret__dropdown-toggler',
                                DROPDOWN          : 'sek-dropdown-menu'
                          },
                          _caret_title_maybe_flip = function( $_el, _direction, _old_direction ) {
                                $.each( $_el, function() {
                                    var $_el               = $(this),
                                        $_a                = $_el.find( 'a' ).first();

                                    if ( 1 == $_a.length ) {
                                          $_a.toggleClass( ClassName.CARET_TITLE_FLIP, _direction == Direction._OPPOSITE  );
                                    }
                                });
                          },
                          _setOpenDirection       = function( _direction ) {
                                //retrieve the old direction => used to remove the old direction class
                                var _old_direction = _direction == Direction._OPPOSITE ? Direction._DEFAULT : Direction._OPPOSITE;

                                //tell the dropdown to open on the direction _direction (hence remove the old direction class)
                                $_dropdown.removeClass( ClassName.OPEN_PREFIX + _old_direction ).addClass( ClassName.OPEN_PREFIX + _direction );
                                if ( $_el.hasClass( ClassName.DD_SUBMENU ) ) {
                                      _caret_title_maybe_flip( $_el, _direction, _old_direction );
                                      //make the first level submenus caret inherit this
                                      _caret_title_maybe_flip( $_dropdown.children( '.' + ClassName.DD_SUBMENU ), _direction, _old_direction );
                                }
                          };

                      //snake inheritance
                      if ( $_dropdown.parent().closest( '.'+ClassName.DROPDOWN ).hasClass( ClassName.OPEN_PREFIX + Direction._OPPOSITE ) ) {
                            //open on the opposite direction
                            _setOpenDirection( Direction._OPPOSITE );
                      } else {
                            //open on the default direction
                            _setOpenDirection( Direction._DEFAULT );
                      }

                      //let's compute on which side open the dropdown
                      if ( $_dropdown.offset().left + $_dropdown.width() > $(window).width() ) {
                            //open on the left
                            _setOpenDirection( 'left' );
                      } else if ( $_dropdown.offset().left < 0 ) {
                            //open on the right
                            _setOpenDirection( 'right' );
                      }
                }//_maybe_move
          };//dropdownPlacement

          //FireAll
          dropdownMenuOnHover();
          dropdownPlacement();
    },

    SimpleCollapse = function() {
        var NAME = 'sekCollapse',
            DATA_KEY = 'sek.sekCollapse',
            EVENT_KEY = "." + DATA_KEY,
            TRANSITION_DURATION = 400,
            DATA_API_KEY = '.data-api',
            Event = {
              SHOW: "show" + EVENT_KEY,
              SHOWN: "shown" + EVENT_KEY,
              HIDE: "hide" + EVENT_KEY,
              HIDDEN: "hidden" + EVENT_KEY,
              CLICK_DATA_API: "click" + EVENT_KEY + DATA_API_KEY
            },
            ClassName = {
              SHOW: 'show',
              COLLAPSE: 'sek-collapse',
              COLLAPSING: 'sek-collapsing',
              COLLAPSED: 'sek-collapsed'
            },
            Selector = {
              ACTIVES: '.show, .sek-collapsing',
              DATA_TOGGLE: '[data-sek-toggle="sek-collapse"]'
            },
            _onSlidingCompleteResetCSS = function( $_el ) {
                  $_el   = $_el ? $_el : $(this);
                  $_el.css({
                        'display'    : '',
                        'paddingTop' : '',
                        'marginTop' : '',
                        'paddingBottom' : '',
                        'marginBottom' : '',
                        'height' : ''
                  });
            };

          //bind
          $(document).on( Event.CLICK_DATA_API, Selector.DATA_TOGGLE, function (event) {
                // preventDefault only for <a> elements (which change the URL) not inside the collapsible element
                if (event.currentTarget.tagName === 'A') {
                      event.preventDefault();
                }

                var $toggler             = $(this),
                   //get the data toggle
                   _collapsible_selector = $toggler.data('target');

                $(_collapsible_selector).each( function () {
                      var $collapsible = $(this),
                          collapse = $collapsible.hasClass(ClassName.SHOW);

                      $collapsible.stop()[ collapse ? 'slideUp' : 'slideDown' ]({
                            duration: TRANSITION_DURATION,
                            start : function() {
                                  $collapsible.addClass(ClassName.COLLAPSING).trigger( collapse ? Event.HIDE : Event.SHOW );
                                  if ( collapse ) {
                                      $toggler.addClass( ClassName.COLLAPSED ).attr( 'aria-expanded', 'false' );
                                  } else {
                                      $toggler.removeClass( ClassName.COLLAPSED ).attr( 'aria-expanded', 'true' );
                                  }
                            },
                            complete: function() {
                                  var removeClass,
                                      addClass,
                                      event;

                                  if ( collapse ) {
                                        removeClass = ClassName.SHOW;
                                        addClass    = ClassName.COLLAPSE;
                                        event       = Event.HIDDEN;
                                  } else {
                                        removeClass = ClassName.COLLAPSE;
                                        addClass    = ClassName.SHOW;
                                        event       = Event.SHOWN;
                                  }
                                  $collapsible.removeClass(ClassName.COLLAPSING + ' ' + removeClass).addClass( addClass ).trigger(event);
                                  //remove all the inline style added by the slideUp/Down methods
                                  _onSlidingCompleteResetCSS( $collapsible );
                            }
                      });//end slideUp/slideDown
                });//end each
          });//end document bind
    };


    Dropdown();
    SimpleCollapse();

    // handle the mobile hamburger hover effect
    $( document )
          .on( 'mouseenter', '.sek-nav-toggler', function(){ $(this).addClass( 'hovering' ); } )
          .on( 'mouseleave', '.sek-nav-toggler', function(){ $(this).removeClass( 'hovering' ); } )
          .on( 'show.sek.sekCollapse hide.sek.sekCollapse', '.sek-nav-collapse', function() {
                $('[data-target=#'+$(this).attr('id')+']').removeClass( 'hovering' );
                $(window).trigger('scroll');
          });

    // How to have a logo plus an hamburger in mobiles on the same line?
    // => clone the menu module, and append it to the closest sektion-inner wrapper
    // => this way it will occupy 100% of the width
    // => and also the clone inherits the style of the module
    // https://github.com/presscustomizr/nimble-builder/issues/368
    $( document ).on( 'ready', function() {
            var _doMobileMenuSetup = function() {
                  $( '[data-sek-module-type="czr_menu_module"]' ).find('[data-sek-expand-below="yes"]').each( function() {
                        // make sure we don't do the setup twice when customizing
                        if ( true === $(this).data('sek-setup-menu-mobile-expanded-below-done') )
                          return;

                        var $_mobile_menu_module  = $(this).closest('[data-sek-module-type="czr_menu_module"]').clone(true),
                            //create a new id for the mobile menu nav collapse that will used by the button toggler too
                            _new_id = $( '.sek-nav-collapse', this ).attr('id') + '-mobile';

                        $_mobile_menu_module
                              /// place the mobile menu at the end of this sektion inner
                              .appendTo( $(this).closest( '.sek-sektion-inner' ) )
                              //wrap in a convenient div for styling and targeting
                              .wrap( '<div class="sek-col-base sek-mobile-menu-expanded-below" id="'+_new_id+'-wrapper"></div>');

                        // assign the new id to the mobile nav collapse
                        $( '.sek-nav-collapse', '#'+_new_id+'-wrapper' ).attr( 'id', _new_id );
                        // add a data attribute so we can target the mobile menu with dynamic css rules
                        // @needed when coding : https://github.com/presscustomizr/nimble-builder/issues/491
                        $( '.sek-nav-wrap', '#'+_new_id+'-wrapper' ).attr('data-sek-is-mobile-menu', 'yes');
                        // remove the duplicate button
                        $( '.sek-nav-toggler', '#'+_new_id+'-wrapper' ).detach();
                        // update the toggler button so that will now refer to the "cloned" mobile menu
                        $( '.sek-nav-toggler', this ).data( 'target', '#' + _new_id )
                                                     .attr( 'aria-controls', _new_id );
                        // flag setup done
                        $(this).data('sek-setup-menu-mobile-expanded-below-done', true );
                  });//$.each()
            };
            _doMobileMenuSetup();
            // When previewing, react to level refresh
            // This can occur to any level. We listen to the bubbling event on 'body' tag
            $('body').on('sek-level-refreshed sek-modules-refreshed sek-columns-refreshed sek-section-added', function( evt ){
                    // clean the previously duplicated menu if any
                    $('.sek-mobile-menu-expanded-below').remove();
                    _doMobileMenuSetup();
            });
    });
});// global sekFrontLocalized
/* ------------------------------------------------------------------------- *
 *  ACCORDION MODULE
/* ------------------------------------------------------------------------- */
jQuery( function($){
    $( 'body' ).on( 'click sek-expand-accord-item', '.sek-accord-item > .sek-accord-title', function( evt ) {
        //evt.preventDefault();
        //evt.stopPropagation();
        var $item = $(this).closest( '.sek-accord-item'),
            $accordion = $(this).closest( '.sek-accord-wrapper');

        // Note : cast the boolean to a string by adding +''
        if ( "true" == $accordion.data('sek-one-expanded')+'' ) {
            $accordion.find('.sek-accord-item').not( $item ).each( function() {
                  var $current_item = $(this);
                  $current_item.find('.sek-accord-content').stop( true, true ).slideUp( {
                        duration : 200,
                        start : function() {
                              // If already expanded, make sure inline style display:block is set
                              // otherwise, the CSS style display:none will apply first, making the transition brutal.
                              if ( "true" == $current_item.attr('data-sek-expanded')+'' ) {
                                    $current_item.find('.sek-accord-content').css('display', 'block');
                              }
                              $current_item.attr('data-sek-expanded', "false" );
                        }
                  });
            });
        }
        if ( 'sek-expand-accord-item' === evt.type && "true" == $item.attr('data-sek-expanded')+'' ) {
            return;
        } else {
            $item.find('.sek-accord-content').stop( true, true ).slideToggle({
                  duration : 200,
                  start : function() {
                        // If already expanded, make sure inline style display:block is set
                        // otherwise, the CSS style display:none will apply first, making the transition brutal.
                        if ( "true" == $item.attr('data-sek-expanded')+'' ) {
                              $item.find('.sek-accord-content').css('display', 'block');
                        }
                        $item.attr('data-sek-expanded', "false" == $item.attr('data-sek-expanded')+'' ? "true" : "false" );
                        $item.trigger( "true" == $item.attr('data-sek-expanded') ? 'sek-accordion-expanded' : 'sek-accordion-collapsed' );
                  }
            });
        }

    });// on 'click'

    // When customizing, expand the currently edited item
    // @see CZRItemConstructor in api.czrModuleMap.czr_img_slider_collection_child
    if ( window.wp && ! _utils_.isUndefined( wp.customize ) ) {
          wp.customize.preview.bind('sek-item-focus', function( params ) {

                var $itemEl = $('[data-sek-item-id="' + params.item_id +'"]', '.sek-accord-wrapper').first();
                if ( 1 > $itemEl.length )
                  return;

                $itemEl.find('.sek-accord-title').trigger('sek-expand-accord-item');
          });
    }
});
// global sekFrontLocalized
/* ------------------------------------------------------------------------- *
 *  SWIPER CAROUSEL implemented for the simple slider module czr_img_slider_module
 *  dependency : $.fn.nimbleCenterImages()
/* ------------------------------------------------------------------------- */
jQuery( function($){
    var mySwipers = [];
    var triggerSimpleLoad = function( $_imgs ) {
          if ( 0 === $_imgs.length )
            return;

          $_imgs.map( function( _ind, _img ) {
            $(_img).load( function () {
              $(_img).trigger('simple_load');
            });//end load
            if ( $(_img)[0] && $(_img)[0].complete )
              $(_img).load();
          } );//end map
    };//end of fn


    // Each swiper is instantiated with a unique id
    // so that if we have several instance on the same page, they are totally independant.
    // If we don't use a unique Id for swiper + navigation buttons, a click on a button, make all slider move synchronously.
    var doSingleSwiperInstantiation = function() {
          var $swiperWrapper = $(this), swiperClass = 'sek-swiper' + $swiperWrapper.data('sek-swiper-id');
          var swiperParams = {
              // slidesPerView: 3,
              // spaceBetween: 30,
              loop : true === $swiperWrapper.data('sek-loop') && true === $swiperWrapper.data('sek-is-multislide'),//Set to true to enable continuous loop mode
              grabCursor : true === $swiperWrapper.data('sek-is-multislide'),
              on : {
                init : function() {
                    // center images with Nimble wizard when needed
                    if ( 'nimble-wizard' === $swiperWrapper.data('sek-image-layout') ) {
                        $swiperWrapper.find('.sek-carousel-img').each( function() {
                            var $_imgsToSimpleLoad = $(this).nimbleCenterImages({
                                  enableCentering : 1,
                                  zeroTopAdjust: 0,
                                  setOpacityWhenCentered : false,//will set the opacity to 1
                                  oncustom : [ 'simple_load', 'smartload', 'sek-nimble-refreshed' ]
                            })
                            //images with src which starts with "data" are our smartload placeholders
                            //we don't want to trigger the simple_load on them
                            //the centering, will be done on the smartload event (see onCustom above)
                            .find( 'img:not([src^="data"])' );

                            //trigger the simple load
                            _utils_.delay( function() {
                                triggerSimpleLoad( $_imgsToSimpleLoad );
                            }, 10 );
                        });//each()
                    }
                }
              }//on
          };

          // AUTOPLAY
          if ( true === $swiperWrapper.data('sek-autoplay') ) {
              $.extend( swiperParams, {
                  autoplay : {
                      delay : $swiperWrapper.data('sek-autoplay-delay'),
                      disableOnInteraction : $swiperWrapper.data('sek-pause-on-hover')
                  }
              });
          } else {
              $.extend( swiperParams, {
                  autoplay : {
                      delay : 999999999//<= the autoplay:false doesn't seem to work...
                  }
              });
          }

          // NAVIGATION ARROWS && PAGINATION DOTS
          if ( true === $swiperWrapper.data('sek-is-multislide') ) {
              if ( _utils_.contains( ['arrows_dots', 'arrows'], $swiperWrapper.data('sek-navtype') ) ) {
                  $.extend( swiperParams, {
                      navigation: {
                        nextEl: '.sek-swiper-next' + $swiperWrapper.data('sek-swiper-id'),
                        prevEl: '.sek-swiper-prev' + $swiperWrapper.data('sek-swiper-id')
                      }
                  });
              }
              if ( _utils_.contains( ['arrows_dots', 'dots'], $swiperWrapper.data('sek-navtype') ) ) {
                  $.extend( swiperParams, {
                      pagination: {
                        el: '.swiper-pagination' + $swiperWrapper.data('sek-swiper-id'),
                        clickable: true,
                      }
                  });
              }
          }

          mySwipers.push( new Swiper(
              '.' + swiperClass,//$(this)[0],
              swiperParams
          ));
    };
    var doAllSwiperInstanciation = function() {
          $('.sektion-wrapper').find('[data-sek-swiper-id]').each( function() {
                doSingleSwiperInstantiation.call($(this));
          });
    };

    // On custom events
    $( 'body').on( 'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-level-refreshed', '[data-sek-level="location"]',
          function() {
            if ( ! _utils_.isEmpty( mySwipers ) ) {
                  _utils_.each( mySwipers, function( _swiperInstance ){
                        _swiperInstance.destroy();
                  });
            }
            mySwipers = [];
            doAllSwiperInstanciation();

            $(this).find('.swiper-container img').each( function() {
                  $(this).trigger('sek-nimble-refreshed');
            });
          }
    );

    // When the stylesheet is refreshed, update the centering with a custom event
    // this is needed when setting the custom height of the slider wrapper
    $( 'body').on( 'sek-stylesheet-refreshed', '[data-sek-module-type="czr_img_slider_module"]',
          function() {
            $(this).find('.swiper-container img').each( function() {
                  $(this).trigger('sek-nimble-refreshed');
            });
          }
    );


    // on load
    $('.sektion-wrapper').find('.swiper-container').each( function() {
          doAllSwiperInstanciation();
    });


    // Action on click
    // $( 'body').on( 'click', '[data-sek-module-type="czr_img_slider_module"]', function(evt ) {
    //         // $(this).find('[data-sek-swiper-id]').each( function() {
    //         //       $(this).trigger('sek-nimble-refreshed');
    //         // });
    //       }
    // );


    // Behaviour on mouse hover
    // @seehttps://stackoverflow.com/questions/53028089/swiper-autoplay-stop-the-swiper-when-you-move-the-mouse-cursor-and-start-playba
    $('.swiper-slide').on('mouseover mouseout', function( evt ) {
        var swiperInstance = $(this).closest('.swiper-container')[0].swiper;
        if ( ! _utils_.isUndefined( swiperInstance ) && true === swiperInstance.params.autoplay.disableOnInteraction ) {
            switch( evt.type ) {
                case 'mouseover' :
                    swiperInstance.autoplay.stop();
                break;
                case 'mouseout' :
                    swiperInstance.autoplay.start();
                break;
            }
        }
    });

    // When customizing, focus on the currently expanded / edited item
    // @see CZRItemConstructor in api.czrModuleMap.czr_img_slider_collection_child
    if ( window.wp && ! _utils_.isUndefined( wp.customize ) ) {
          wp.customize.preview.bind('sek-item-focus', function( params ) {

                var $itemEl = $('[data-sek-item-id="' + params.item_id +'"]', '.swiper-container').first();
                if ( 1 > $itemEl.length )
                  return;
                var $swiperContainer = $itemEl.closest('.swiper-container');
                if ( 1 > $swiperContainer.length )
                  return;

                var activeSwiperInstance = $itemEl.closest('.swiper-container')[0].swiper;

                if ( _utils_.isUndefined( activeSwiperInstance ) )
                  return;
                // we can't rely on internal indexing system of swipe, because it uses duplicate item when infinite looping is enabled
                // jQuery is our friend
                var slideIndex = $( '.swiper-slide', $swiperContainer ).index( $itemEl );
                //http://idangero.us/swiper/api/#methods
                //mySwiper.slideTo(index, speed, runCallbacks);
                activeSwiperInstance.slideTo( slideIndex, 100 );
          });
    }
});

// global sekFrontLocalized
/* ------------------------------------------------------------------------- *
 *  LIGHT BOX WITH MAGNIFIC POPUP
/* ------------------------------------------------------------------------- */
jQuery(function($){
      $('[data-sek-module-type="czr_image_module"]').each( function() {
            $linkCandidate = $(this).find('.sek-link-to-img-lightbox');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $linkCandidate.length < 1 || 'string' !== typeof( $linkCandidate[0].protocol ) || -1 !== $linkCandidate[0].protocol.indexOf('javascript') )
              return;
            if ( 'function' !== typeof( $.fn.magnificPopup ) )
              return;
            try { $linkCandidate.magnificPopup({
                type: 'image',
                closeOnContentClick: true,
                closeBtnInside: true,
                fixedContentPos: true,
                mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
                image: {
                  verticalFit: true
                },
                zoom: {
                  enabled: true,
                  duration: 300 // don't foget to change the duration also in CSS
                }
            }); } catch( er ) {
                  if ( typeof window.console.log === 'function' ) {
                        console.log( er );
                  }
            }
      });
});


/* ------------------------------------------------------------------------- *
 *  SMARTLOAD
/* ------------------------------------------------------------------------- */
jQuery(function($){
      $('.sektion-wrapper').each( function() {
            try { $(this).nimbleLazyLoad(); } catch( er ) {
                  if ( typeof window.console.log === 'function' ) {
                        console.log( er );
                  }
            }
      });
});


/* ------------------------------------------------------------------------- *
 *  BG PARALLAX
/* ------------------------------------------------------------------------- */
jQuery(function($){
      $('[data-sek-bg-parallax="true"]').each( function() {
            $(this).parallaxBg( { parallaxForce : $(this).data('sek-parallax-force') } );
      });
      var _setParallaxWhenCustomizing = function() {
            $(this).parallaxBg( { parallaxForce : $(this).data('sek-parallax-force') } );
            // hack => always trigger a 'resize' event with a small delay to make sure bg positions are ok
            setTimeout( function() {
                 $('body').trigger('resize');
            }, 500 );
      };
      // When previewing, react to level refresh
      // This can occur to any level. We listen to the bubbling event on 'body' tag
      // and salmon up to maybe instantiate any missing candidate
      // Example : when a preset_section is injected
      $('body').on('sek-level-refreshed sek-section-added', function( evt ){
            if ( "true" === $(this).data('sek-bg-parallax') ) {
                  _setParallaxWhenCustomizing.call(this);
            } else {
                  $(this).find('[data-sek-bg-parallax="true"]').each( function() {
                        _setParallaxWhenCustomizing.call(this);
                  });
            }
      });
});


/* ------------------------------------------------------------------------- *
 *  FITTEXT
/* ------------------------------------------------------------------------- */
jQuery( function($){
    var doFitText = function() {
          $(".sek-module-placeholder").each( function() {
                $(this).fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
          });
          // Delegate instantiation
          $('.sektion-wrapper').on(
                'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-level-refreshed',
                'div[data-sek-level="section"]',
                function( evt ) {
                      $(this).find(".sek-module-placeholder").fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
                }
          );

    };
    //doFitText();
    // if ( 'function' == typeof(_) && window.wp && ! _utils_.isUndefined( wp.customize ) ) {
    //     wp.customize.selectiveRefresh.bind('partial-content-rendered' , function() {
    //         doFitText();
    //     });
    // }

    // does the same as new URL(url)
    // but support IE.
    // @see https://stackoverflow.com/questions/736513/how-do-i-parse-a-url-into-hostname-and-path-in-javascript
    // @see https://gist.github.com/acdcjunior/9820040
    // @see https://developer.mozilla.org/en-US/docs/Web/API/URL#Properties
    var parseURL = function(url) {
          var parser = document.createElement("a");
          parser.href = url;
          // IE 8 and 9 dont load the attributes "protocol" and "host" in case the source URL
          // is just a pathname, that is, "/example" and not "http://domain.com/example".
          parser.href = parser.href;

          // copies all the properties to this object
          var properties = ['host', 'hostname', 'hash', 'href', 'port', 'protocol', 'search'];
          for (var i = 0, n = properties.length; i < n; i++) {
            this[properties[i]] = parser[properties[i]];
          }

          // pathname is special because IE takes the "/" of the starting of pathname
          this.pathname = (parser.pathname.charAt(0) !== "/" ? "/" : "") + parser.pathname;
    };

    var $root = $('html, body');
    var maybeScrollToAnchor = function( evt ){
          // problem to solve : users want to define anchor links that work inside a page, but also from other pages.
          // @see https://github.com/presscustomizr/nimble-builder/issues/413
          var clickedItemUrl = $(this).attr('href');
          if ( '' === clickedItemUrl || null === clickedItemUrl || 'string' !== typeof( clickedItemUrl ) || -1 === clickedItemUrl.indexOf('#') )
            return;

          // an anchor link looks like this : http://mysite.com/contact/#anchor
          var itemURLObject = new parseURL( clickedItemUrl ),
              _currentPageUrl = new parseURL( window.document.location.href );

          if( itemURLObject.pathname !== _currentPageUrl.pathname )
            return;
          if( 'string' !== typeof(itemURLObject.hash) || '' === itemURLObject.hash )
            return;
          var $nimbleTargetCandidate = $('[data-sek-level="location"]' ).find( '[id="' + itemURLObject.hash.replace('#','') + '"]');
          if ( 1 !== $nimbleTargetCandidate.length )
            return;

          evt.preventDefault();
          $root.animate({ scrollTop : $nimbleTargetCandidate.offset().top - 150 }, 400 );
    };

    // animate menu item to Nimble anchors
    $('body').find('.menu-item' ).on( 'click', 'a', maybeScrollToAnchor );

    // animate an anchor link inside Nimble sections
    // fixes https://github.com/presscustomizr/nimble-builder/issues/443
    $('[data-sek-level="location"]' ).on( 'click', 'a', maybeScrollToAnchor );
});
