
(function() {
  var root = typeof self == 'object' && self.self === self && self ||
            typeof global == 'object' && global.global === global && global ||
            this ||
            {};
  var previousUnderscore = root._;
  var ArrayProto = Array.prototype, ObjProto = Object.prototype;
  var SymbolProto = typeof Symbol !== 'undefined' ? Symbol.prototype : null;
  var push = ArrayProto.push,
      slice = ArrayProto.slice,
      toString = ObjProto.toString,
      hasOwnProperty = ObjProto.hasOwnProperty;
  var nativeIsArray = Array.isArray,
      nativeKeys = Object.keys,
      nativeCreate = Object.create;
  var Ctor = function(){};
  var _ = function(obj) {
    if (obj instanceof _) return obj;
    if (!(this instanceof _)) return new _(obj);
    this._wrapped = obj;
  };
  if (typeof exports != 'undefined' && !exports.nodeType) {
    if (typeof module != 'undefined' && !module.nodeType && module.exports) {
      exports = module.exports = _;
    }
    exports._utils_ = _;
  } else {
    root._utils_ = _;
  }
  _.VERSION = '1.9.1';
  var optimizeCb = function(func, context, argCount) {
    if (context === void 0) return func;
    switch (argCount == null ? 3 : argCount) {
      case 1: return function(value) {
        return func.call(context, value);
      };
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
  var cb = function(value, context, argCount) {
    if (_.iteratee !== builtinIteratee) return _.iteratee(value, context);
    if (value == null) return _.identity;
    if (_.isFunction(value)) return optimizeCb(value, context, argCount);
    if (_.isObject(value) && !_.isArray(value)) return _.matcher(value);
    return _.property(value);
  };
  _.iteratee = builtinIteratee = function(value, context) {
    return cb(value, context, Infinity);
  };
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
  var MAX_ARRAY_INDEX = Math.pow(2, 53) - 1;
  var getLength = shallowProperty('length');
  var isArrayLike = function(collection) {
    var length = getLength(collection);
    return typeof length == 'number' && length >= 0 && length <= MAX_ARRAY_INDEX;
  };
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
  var createReduce = function(dir) {
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
  _.reduce = _.foldl = _.inject = createReduce(1);
  _.reduceRight = _.foldr = createReduce(-1);
  _.find = _.detect = function(obj, predicate, context) {
    var keyFinder = isArrayLike(obj) ? _.findIndex : _.findKey;
    var key = keyFinder(obj, predicate, context);
    if (key !== void 0 && key !== -1) return obj[key];
  };
  _.filter = _.select = function(obj, predicate, context) {
    var results = [];
    predicate = cb(predicate, context);
    _.each(obj, function(value, index, list) {
      if (predicate(value, index, list)) results.push(value);
    });
    return results;
  };
  _.reject = function(obj, predicate, context) {
    return _.filter(obj, _.negate(cb(predicate)), context);
  };
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
  _.contains = _.includes = _.include = function(obj, item, fromIndex, guard) {
    if (!isArrayLike(obj)) obj = _.values(obj);
    if (typeof fromIndex != 'number' || guard) fromIndex = 0;
    return _.indexOf(obj, item, fromIndex) >= 0;
  };
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
  _.pluck = function(obj, key) {
    return _.map(obj, _.property(key));
  };
  _.where = function(obj, attrs) {
    return _.filter(obj, _.matcher(attrs));
  };
  _.findWhere = function(obj, attrs) {
    return _.find(obj, _.matcher(attrs));
  };
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
  _.shuffle = function(obj) {
    return _.sample(obj, Infinity);
  };
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
  _.groupBy = group(function(result, value, key) {
    if (has(result, key)) result[key].push(value); else result[key] = [value];
  });
  _.indexBy = group(function(result, value, key) {
    result[key] = value;
  });
  _.countBy = group(function(result, value, key) {
    if (has(result, key)) result[key]++; else result[key] = 1;
  });

  var reStrSymbol = /[^\ud800-\udfff]|[\ud800-\udbff][\udc00-\udfff]|[\ud800-\udfff]/g;
  _.toArray = function(obj) {
    if (!obj) return [];
    if (_.isArray(obj)) return slice.call(obj);
    if (_.isString(obj)) {
      return obj.match(reStrSymbol);
    }
    if (isArrayLike(obj)) return _.map(obj, _.identity);
    return _.values(obj);
  };
  _.size = function(obj) {
    if (obj == null) return 0;
    return isArrayLike(obj) ? obj.length : _.keys(obj).length;
  };
  _.partition = group(function(result, value, pass) {
    result[pass ? 0 : 1].push(value);
  }, true);
  _.first = _.head = _.take = function(array, n, guard) {
    if (array == null || array.length < 1) return n == null ? void 0 : [];
    if (n == null || guard) return array[0];
    return _.initial(array, array.length - n);
  };
  _.initial = function(array, n, guard) {
    return slice.call(array, 0, Math.max(0, array.length - (n == null || guard ? 1 : n)));
  };
  _.last = function(array, n, guard) {
    if (array == null || array.length < 1) return n == null ? void 0 : [];
    if (n == null || guard) return array[array.length - 1];
    return _.rest(array, Math.max(0, array.length - n));
  };
  _.rest = _.tail = _.drop = function(array, n, guard) {
    return slice.call(array, n == null || guard ? 1 : n);
  };
  _.compact = function(array) {
    return _.filter(array, Boolean);
  };
  var flatten = function(input, shallow, strict, output) {
    output = output || [];
    var idx = output.length;
    for (var i = 0, length = getLength(input); i < length; i++) {
      var value = input[i];
      if (isArrayLike(value) && (_.isArray(value) || _.isArguments(value))) {
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
  _.flatten = function(array, shallow) {
    return flatten(array, shallow, false);
  };
  _.without = restArguments(function(array, otherArrays) {
    return _.difference(array, otherArrays);
  });
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
  _.union = restArguments(function(arrays) {
    return _.uniq(flatten(arrays, true, true));
  });
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
  _.difference = restArguments(function(array, rest) {
    rest = flatten(rest, true, true);
    return _.filter(array, function(value){
      return !_.contains(rest, value);
    });
  });
  _.unzip = function(array) {
    var length = array && _.max(array, getLength).length || 0;
    var result = Array(length);

    for (var index = 0; index < length; index++) {
      result[index] = _.pluck(array, index);
    }
    return result;
  };
  _.zip = restArguments(_.unzip);
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
  _.findIndex = createPredicateIndexFinder(1);
  _.findLastIndex = createPredicateIndexFinder(-1);
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
  _.indexOf = createIndexFinder(1, _.findIndex, _.sortedIndex);
  _.lastIndexOf = createIndexFinder(-1, _.findLastIndex);
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
  _.chunk = function(array, count) {
    if (count == null || count < 1) return [];
    var result = [];
    var i = 0, length = array.length;
    while (i < length) {
      result.push(slice.call(array, i, i += count));
    }
    return result;
  };
  var executeBound = function(sourceFunc, boundFunc, context, callingContext, args) {
    if (!(callingContext instanceof boundFunc)) return sourceFunc.apply(context, args);
    var self = baseCreate(sourceFunc.prototype);
    var result = sourceFunc.apply(self, args);
    if (_.isObject(result)) return result;
    return self;
  };
  _.bind = restArguments(function(func, context, args) {
    if (!_.isFunction(func)) throw new TypeError('Bind must be called on a function');
    var bound = restArguments(function(callArgs) {
      return executeBound(func, bound, context, this, args.concat(callArgs));
    });
    return bound;
  });
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
  _.bindAll = restArguments(function(obj, keys) {
    keys = flatten(keys, false, false);
    var index = keys.length;
    if (index < 1) throw new Error('bindAll must be passed function names');
    while (index--) {
      var key = keys[index];
      obj[key] = _.bind(obj[key], obj);
    }
  });
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
  _.delay = restArguments(function(func, wait, args) {
    return setTimeout(function() {
      return func.apply(null, args);
    }, wait);
  });
  _.defer = _.partial(_.delay, _, 1);
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
  _.wrap = function(func, wrapper) {
    return _.partial(wrapper, func);
  };
  _.negate = function(predicate) {
    return function() {
      return !predicate.apply(this, arguments);
    };
  };
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
  _.after = function(times, func) {
    return function() {
      if (--times < 1) {
        return func.apply(this, arguments);
      }
    };
  };
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
  _.once = _.partial(_.before, 2);

  _.restArguments = restArguments;
  var hasEnumBug = !{toString: null}.propertyIsEnumerable('toString');
  var nonEnumerableProps = ['valueOf', 'isPrototypeOf', 'toString',
    'propertyIsEnumerable', 'hasOwnProperty', 'toLocaleString'];

  var collectNonEnumProps = function(obj, keys) {
    var nonEnumIdx = nonEnumerableProps.length;
    var constructor = obj.constructor;
    var proto = _.isFunction(constructor) && constructor.prototype || ObjProto;
    var prop = 'constructor';
    if (has(obj, prop) && !_.contains(keys, prop)) keys.push(prop);

    while (nonEnumIdx--) {
      prop = nonEnumerableProps[nonEnumIdx];
      if (prop in obj && obj[prop] !== proto[prop] && !_.contains(keys, prop)) {
        keys.push(prop);
      }
    }
  };
  _.keys = function(obj) {
    if (!_.isObject(obj)) return [];
    if (nativeKeys) return nativeKeys(obj);
    var keys = [];
    for (var key in obj) if (has(obj, key)) keys.push(key);
    if (hasEnumBug) collectNonEnumProps(obj, keys);
    return keys;
  };
  _.allKeys = function(obj) {
    if (!_.isObject(obj)) return [];
    var keys = [];
    for (var key in obj) keys.push(key);
    if (hasEnumBug) collectNonEnumProps(obj, keys);
    return keys;
  };
  _.values = function(obj) {
    var keys = _.keys(obj);
    var length = keys.length;
    var values = Array(length);
    for (var i = 0; i < length; i++) {
      values[i] = obj[keys[i]];
    }
    return values;
  };
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
  _.pairs = function(obj) {
    var keys = _.keys(obj);
    var length = keys.length;
    var pairs = Array(length);
    for (var i = 0; i < length; i++) {
      pairs[i] = [keys[i], obj[keys[i]]];
    }
    return pairs;
  };
  _.invert = function(obj) {
    var result = {};
    var keys = _.keys(obj);
    for (var i = 0, length = keys.length; i < length; i++) {
      result[obj[keys[i]]] = keys[i];
    }
    return result;
  };
  _.functions = _.methods = function(obj) {
    var names = [];
    for (var key in obj) {
      if (_.isFunction(obj[key])) names.push(key);
    }
    return names.sort();
  };
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
  _.extend = createAssigner(_.allKeys);
  _.extendOwn = _.assign = createAssigner(_.keys);
  _.findKey = function(obj, predicate, context) {
    predicate = cb(predicate, context);
    var keys = _.keys(obj), key;
    for (var i = 0, length = keys.length; i < length; i++) {
      key = keys[i];
      if (predicate(obj[key], key, obj)) return key;
    }
  };
  var keyInObj = function(value, key, obj) {
    return key in obj;
  };
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
  _.defaults = createAssigner(_.allKeys, true);
  _.create = function(prototype, props) {
    var result = baseCreate(prototype);
    if (props) _.extendOwn(result, props);
    return result;
  };
  _.clone = function(obj) {
    if (!_.isObject(obj)) return obj;
    return _.isArray(obj) ? obj.slice() : _.extend({}, obj);
  };
  _.tap = function(obj, interceptor) {
    interceptor(obj);
    return obj;
  };
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
  var eq, deepEq;
  eq = function(a, b, aStack, bStack) {
    if (a === b) return a !== 0 || 1 / a === 1 / b;
    if (a == null || b == null) return false;
    if (a !== a) return b !== b;
    var type = typeof a;
    if (type !== 'function' && type !== 'object' && typeof b != 'object') return false;
    return deepEq(a, b, aStack, bStack);
  };
  deepEq = function(a, b, aStack, bStack) {
    if (a instanceof _) a = a._wrapped;
    if (b instanceof _) b = b._wrapped;
    var className = toString.call(a);
    if (className !== toString.call(b)) return false;
    switch (className) {
      case '[object RegExp]':
      case '[object String]':
        return '' + a === '' + b;
      case '[object Number]':
        if (+a !== +a) return +b !== +b;
        return +a === 0 ? 1 / +a === 1 / b : +a === +b;
      case '[object Date]':
      case '[object Boolean]':
        return +a === +b;
      case '[object Symbol]':
        return SymbolProto.valueOf.call(a) === SymbolProto.valueOf.call(b);
    }

    var areArrays = className === '[object Array]';
    if (!areArrays) {
      if (typeof a != 'object' || typeof b != 'object') return false;
      var aCtor = a.constructor, bCtor = b.constructor;
      if (aCtor !== bCtor && !(_.isFunction(aCtor) && aCtor instanceof aCtor &&
                               _.isFunction(bCtor) && bCtor instanceof bCtor)
                          && ('constructor' in a && 'constructor' in b)) {
        return false;
      }
    }
    aStack = aStack || [];
    bStack = bStack || [];
    var length = aStack.length;
    while (length--) {
      if (aStack[length] === a) return bStack[length] === b;
    }
    aStack.push(a);
    bStack.push(b);
    if (areArrays) {
      length = a.length;
      if (length !== b.length) return false;
      while (length--) {
        if (!eq(a[length], b[length], aStack, bStack)) return false;
      }
    } else {
      var keys = _.keys(a), key;
      length = keys.length;
      if (_.keys(b).length !== length) return false;
      while (length--) {
        key = keys[length];
        if (!(has(b, key) && eq(a[key], b[key], aStack, bStack))) return false;
      }
    }
    aStack.pop();
    bStack.pop();
    return true;
  };
  _.isEqual = function(a, b) {
    return eq(a, b);
  };
  _.isEmpty = function(obj) {
    if (obj == null) return true;
    if (isArrayLike(obj) && (_.isArray(obj) || _.isString(obj) || _.isArguments(obj))) return obj.length === 0;
    return _.keys(obj).length === 0;
  };
  _.isElement = function(obj) {
    return !!(obj && obj.nodeType === 1);
  };
  _.isArray = nativeIsArray || function(obj) {
    return toString.call(obj) === '[object Array]';
  };
  _.isObject = function(obj) {
    var type = typeof obj;
    return type === 'function' || type === 'object' && !!obj;
  };
  _.each(['Arguments', 'Function', 'String', 'Number', 'Date', 'RegExp', 'Error', 'Symbol', 'Map', 'WeakMap', 'Set', 'WeakSet'], function(name) {
    _['is' + name] = function(obj) {
      return toString.call(obj) === '[object ' + name + ']';
    };
  });
  if (!_.isArguments(arguments)) {
    _.isArguments = function(obj) {
      return has(obj, 'callee');
    };
  }
  var nodelist = root.document && root.document.childNodes;
  if (typeof /./ != 'function' && typeof Int8Array != 'object' && typeof nodelist != 'function') {
    _.isFunction = function(obj) {
      return typeof obj == 'function' || false;
    };
  }
  _.isFinite = function(obj) {
    return !_.isSymbol(obj) && isFinite(obj) && !isNaN(parseFloat(obj));
  };
  _.isNaN = function(obj) {
    return _.isNumber(obj) && isNaN(obj);
  };
  _.isBoolean = function(obj) {
    return obj === true || obj === false || toString.call(obj) === '[object Boolean]';
  };
  _.isNull = function(obj) {
    return obj === null;
  };
  _.isUndefined = function(obj) {
    return obj === void 0;
  };
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
  _.noConflict = function() {
    root._ = previousUnderscore;
    return this;
  };
  _.identity = function(value) {
    return value;
  };
  _.constant = function(value) {
    return function() {
      return value;
    };
  };

  _.noop = function(){};
  _.property = function(path) {
    if (!_.isArray(path)) {
      return shallowProperty(path);
    }
    return function(obj) {
      return deepGet(obj, path);
    };
  };
  _.propertyOf = function(obj) {
    if (obj == null) {
      return function(){};
    }
    return function(path) {
      return !_.isArray(path) ? obj[path] : deepGet(obj, path);
    };
  };
  _.matcher = _.matches = function(attrs) {
    attrs = _.extendOwn({}, attrs);
    return function(obj) {
      return _.isMatch(obj, attrs);
    };
  };
  _.times = function(n, iteratee, context) {
    var accum = Array(Math.max(0, n));
    iteratee = optimizeCb(iteratee, context, 1);
    for (var i = 0; i < n; i++) accum[i] = iteratee(i);
    return accum;
  };
  _.random = function(min, max) {
    if (max == null) {
      max = min;
      min = 0;
    }
    return min + Math.floor(Math.random() * (max - min + 1));
  };
  _.now = Date.now || function() {
    return new Date().getTime();
  };
  var escapeMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#x27;',
    '`': '&#x60;'
  };
  var unescapeMap = _.invert(escapeMap);
  var createEscaper = function(map) {
    var escaper = function(match) {
      return map[match];
    };
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
  var idCounter = 0;
  _.uniqueId = function(prefix) {
    var id = ++idCounter + '';
    return prefix ? prefix + id : id;
  };
  _.templateSettings = {
    evaluate: /<%([\s\S]+?)%>/g,
    interpolate: /<%=([\s\S]+?)%>/g,
    escape: /<%-([\s\S]+?)%>/g
  };
  var noMatch = /(.)^/;
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
  _.template = function(text, settings, oldSettings) {
    if (!settings && oldSettings) settings = oldSettings;
    settings = _.defaults({}, settings, _.templateSettings);
    var matcher = RegExp([
      (settings.escape || noMatch).source,
      (settings.interpolate || noMatch).source,
      (settings.evaluate || noMatch).source
    ].join('|') + '|$', 'g');
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
      return match;
    });
    source += "';\n";
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
    var argument = settings.variable || 'obj';
    template.source = 'function(' + argument + '){\n' + source + '}';

    return template;
  };
  _.chain = function(obj) {
    var instance = _(obj);
    instance._chain = true;
    return instance;
  };
  var chainResult = function(instance, obj) {
    return instance._chain ? _(obj).chain() : obj;
  };
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
  _.mixin(_);
  _.each(['pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      var obj = this._wrapped;
      method.apply(obj, arguments);
      if ((name === 'shift' || name === 'splice') && obj.length === 0) delete obj[0];
      return chainResult(this, obj);
    };
  });
  _.each(['concat', 'join', 'slice'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      return chainResult(this, method.apply(this._wrapped, arguments));
    };
  });
  _.prototype.value = function() {
    return this._wrapped;
  };
  _.prototype.valueOf = _.prototype.toJSON = _.prototype.value;

  _.prototype.toString = function() {
    return String(this._wrapped);
  };
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
    var compressor = kompressor || 1,
        settings = $.extend({
          'minFontSize' : Number.NEGATIVE_INFINITY,
          'maxFontSize' : Number.POSITIVE_INFINITY
        }, options);

    return this.each(function(){
      var $this = $(this);
      var resizer = function () {
        $this.css('font-size', Math.max(Math.min($this.width() / (compressor*10), parseFloat(settings.maxFontSize)), parseFloat(settings.minFontSize)));
      };
      resizer();
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
 * =================================================== */
(function ( $, window ) {
      var pluginName = 'nimbleLazyLoad',
          defaults = {
                load_all_images_on_first_scroll : false,
                excludeImg : [],
                threshold : 200,
                fadeIn_options : { duration : 400 },
                delaySmartLoadEvent : 0,

          },
          skipImgClass = 'smartload-skip';


      function Plugin( element, options ) {
            this.element = element;
            this.options = $.extend( {}, defaults, options) ;
            if ( _utils_.isArray( this.options.excludeImg ) ) {
                  this.options.excludeImg.push( '.'+skipImgClass );
            } else {
                  this.options.excludeImg = [ '.'+skipImgClass ];
            }

            this._defaults = defaults;
            this._name = pluginName;
            this.init();
      }
      Plugin.prototype.init = function () {
            var self        = this,
                $_ImgOrBackgroundElements   = $( '[data-sek-src]:not('+ this.options.excludeImg.join() +')' , this.element );

            this.increment  = 1;//used to wait a little bit after the first user scroll actions to trigger the timer
            this.timer      = 0;

            $_ImgOrBackgroundElements
                  .addClass( skipImgClass )
                  .bind( 'sek_load_img', {}, function() {
                        self._load_img(this);
                  });
            $(window).scroll( function( _evt ) { self._better_scroll_event_handler( $_ImgOrBackgroundElements, _evt ); } );
            $(window).resize( _utils_.debounce( function( _evt ) { self._maybe_trigger_load( $_ImgOrBackgroundElements, _evt ); }, 100 ) );
            this._maybe_trigger_load( $_ImgOrBackgroundElements );
      };


      /*
      * @param : array of $img
      * @param : current event
      * @return : void
      * scroll event performance enhancer => avoid browser stack if too much scrolls
      */
      Plugin.prototype._better_scroll_event_handler = function( $_ImgOrBackgroundElements , _evt ) {
            var self = this;
            if ( ! this.doingAnimation ) {
                  this.doingAnimation = true;
                  window.requestAnimationFrame(function() {
                        self._maybe_trigger_load( $_ImgOrBackgroundElements , _evt );
                        self.doingAnimation = false;
                  });
            }
      };


      /*
      * @param : array of $img
      * @param : current event
      * @return : void
      */
      Plugin.prototype._maybe_trigger_load = function( $_ImgOrBackgroundElements , _evt ) {
            var self = this,
                _visible_list = $_ImgOrBackgroundElements.filter( function( ind, _el ) { return self._is_visible( _el ,  _evt ); } );
            _visible_list.map( function( ind, _el ) {
                  $(_el).trigger( 'sek_load_img' );
            });
      };


      /*
      * @param single $img object
      * @param : current event
      * @return bool
      * helper to check if an image is the visible ( viewport + custom option threshold)
      */
      Plugin.prototype._is_visible = function( element, _evt ) {
            var $element       = $(element),
                wt = $(window).scrollTop(),
                wb = wt + $(window).height(),
                it  = $element.offset().top,
                ib  = it + $element.height(),
                th = this.options.threshold;
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
                  .load( function () {
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
                        if ( ! $_el.hasClass('sek-lazy-loaded') ) {
                              $_el.addClass('sek-lazy-loaded');
                        }

                        $_el.trigger('smartload');
                        $_el.data('sek-lazy-loaded', true );
                  });//<= create a load() fn
            if ( $jQueryImgToLoad[0].complete ) {
                  $jQueryImgToLoad.load();
            }
            $_el.removeClass('lazy-loading');
      };
      $.fn[pluginName] = function ( options ) {
            return this.each(function () {
                  if (!$.data(this, 'plugin_' + pluginName)) {
                        $.data(this, 'plugin_' + pluginName,
                        new Plugin( this, options ));
                  }
            });
      };
})( jQuery, window );
/* ===================================================
 * jquery.fn.parallaxBg v1.0.0
 * Created in October 2018.
 * Inspired from https://github.com/presscustomizr/front-jquery-plugins/blob/master/jqueryParallax.js
 * ===================================================
*/
(function ( $, window ) {
      var pluginName = 'parallaxBg',
          defaults = {
                parallaxRatio : 0.5,
                parallaxDirection : 1,
                parallaxOverflowHidden : true,
                oncustom : [],//list of event here
                matchMedia : 'only screen and (max-width: 800px)'
          };

      function Plugin( element, options ) {
            this.element         = $(element);
            this.options         = $.extend( {}, defaults, options, this.parseElementDataOptions() ) ;
            this._defaults       = defaults;
            this._name           = pluginName;
            this.init();
      }

      Plugin.prototype.parseElementDataOptions = function () {
            return this.element.data();
      };
      Plugin.prototype.init = function () {
            var self = this;
            this.$_document   = $(document);
            this.$_window     = $(window);
            this.doingAnimation = false;
            _utils_.bindAll( this, 'maybeParallaxMe', 'parallaxMe' );
            $(window).scroll( function(_evt) { self.maybeParallaxMe(); } );
            $(window).resize( _utils_.debounce( function(_evt) { self.maybeParallaxMe(); }, 100 ) );
            self.maybeParallaxMe();
      };

      Plugin.prototype._is_visible = function( _evt ) {
          var $element       = this.element,
              wt = $(window).scrollTop(),
              wb = wt + $(window).height(),
              it  = $element.offset().top,
              ib  = it + $element.outerHeight(),
              threshold = 0;
          if ( _evt && 'scroll' == _evt.type && this.options.load_all_images_on_first_scroll )
            return true;

          return ib >= wt - threshold && it <= wb + threshold;
      };
      /*
      * In order to handle a smooth scroll
      */
      Plugin.prototype.maybeParallaxMe = function() {
            var self = this;
            if ( ! this._is_visible() )
              return;
            if ( _utils_.isFunction( window.matchMedia ) && matchMedia( self.options.matchMedia ).matches ) {
                  this.element.css({'background-position-y' : '', 'background-attachment' : '' });
                  return;
            }

            if ( ! this.doingAnimation ) {
                  this.doingAnimation = true;
                  window.requestAnimationFrame(function() {
                        self.parallaxMe();
                        self.doingAnimation = false;
                  });
            }
      };
      Plugin.prototype.setTopPosition = function( _top_ ) {
            _top_ = _top_ || 0;
            this.element.css({
                  'background-position-y' : ( -1 * _top_ ) + 'px',
                  'background-attachment' : 'fixed',
            });
      };

      Plugin.prototype.parallaxMe = function() {
            /*
            if ( ! ( this.element.hasClass( 'is-selected' ) || this.element.parent( '.is-selected' ).length ) )
              return;
            */
            var $element       = this.element;
            var ratio = this.options.parallaxRatio,
                parallaxDirection = this.options.parallaxDirection,
                ElementDistanceToTop  = $element.offset().top,
                value = ratio * parallaxDirection * ( this.$_document.scrollTop() - ElementDistanceToTop );

            this.setTopPosition( parallaxDirection * value );
      };
      $.fn[pluginName] = function ( options ) {
          return this.each(function () {
              if (!$.data(this, 'plugin_' + pluginName)) {
                  $.data(this, 'plugin_' + pluginName,
                  new Plugin( this, options ));
              }
          });
      };
})( jQuery, window );/* ------------------------------------------------------------------------- *
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
      $('[data-sek-bg-parallax="true"]').parallaxBg();
      $('body').on('sek-level-refreshed sek-section-added', function( evt ){
            if ( "true" === $(this).attr( 'data-sek-bg-parallax' ) ) {
                  $(this).parallaxBg();
            } else {
                  $(this).find('[data-sek-bg-parallax="true"]').parallaxBg();
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
          $('.sektion-wrapper').on(
                'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-level-refreshed',
                'div[data-sek-level="section"]',
                function( evt ) {
                      $(this).find(".sek-module-placeholder").fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
                }
          );

    };
    $('body').find('.sek-menu-module, .menu, .nav' ).on( 'click', '.menu-item [href^="#"]', function( evt ){
          evt.preventDefault();
          var anchorCandidate = $(this).attr('href');
          anchorCandidate = 'string' === typeof( anchorCandidate ) ? anchorCandidate.replace('#','') : '';

          if ( '' !== anchorCandidate || null !== anchorCandidate ) {
                var $anchorCandidate = $('[data-sek-level="location"]' ).find( '[id="' + anchorCandidate + '"]');
                if ( 1 === $anchorCandidate.length ) {
                      $('html, body').animate({
                            scrollTop : $anchorCandidate.offset().top - 150
                      }, 'slow');
                }
          }
    });
});


/* ------------------------------------------------------------------------- *
 *  MENU
/* ------------------------------------------------------------------------- */
jQuery( function($){
    var Dropdown = function() {
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
          $( '.sek-nav .children, .sek-nav .sub-menu' ).addClass( ClassName.DROPDOWN );
          $( '.sek-nav-wrap .page_item_has_children' ).addClass( ClassName.PARENTS );
          $( '.sek-nav' + ' .' + ClassName.DROPDOWN + ' .' + ClassName.PARENTS ).addClass( ClassName.DROPDOWN_SUBMENU );
          var dropdownMenuOnHover = function() {
                var _dropdown_selector = Selector.HOVER_PARENT;

                function _addOpenClass () {
                      var $_el = $(this);
                      var _debounced_addOpenClass = _utils_.debounce( function() {
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
                $( document )
                    .on( 'mouseenter', _dropdown_selector, _addOpenClass )
                    .on( 'mouseleave', _dropdown_selector , _removeOpenClass );
          },
          dropdownPlacement = function() {
                var isRTL = 'rtl' === $('html').attr('dir'),
                    doingAnimation = false;

                $(window)
                    .on( 'resize', function() {
                            if ( ! doingAnimation ) {
                                  doingAnimation = true;
                                  window.requestAnimationFrame(function() {
                                    $( Selector.SNAKE_PARENTS+'.'+ClassName.SHOW)
                                        .trigger(Event.PLACE_ME);
                                    doingAnimation = false;
                                  });
                            }

                    });

                $( document )
                    .on( Event.PLACE_ALL, function() {
                                $( Selector.SNAKE_PARENTS )
                                    .trigger(Event.PLACE_ME);
                    })
                    .on( Event.SHOWN+' '+Event.PLACE_ME, Selector.SNAKE_PARENTS, function(evt) {
                      evt.stopPropagation();
                      _do_snake( $(this), evt );
                    });
                function _do_snake( $_el, evt ) {
                      if ( !( evt && evt.namespace && DATA_KEY === evt.namespace ) ) {
                            return;
                      }

                      var $_this       = $_el,
                          $_dropdown   = $_this.children( '.'+ClassName.DROPDOWN );

                      if ( !$_dropdown.length ) {
                            return;
                      }
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
                      $_dropdown.css({
                        'zIndex'  : '',
                        'display' : ''
                      });
                      $_el.css( 'overflow', '' );
                }//_so_snake


                function _maybe_move( $_dropdown, $_el ) {
                      var Direction          = isRTL ? {
                                _DEFAULT          : 'left',
                                _OPPOSITE         : 'right'
                          } : {
                                _DEFAULT          : 'right',
                                _OPPOSITE         : 'left'
                          },
                          ClassName          = {
                                OPEN_PREFIX       : 'open-',
                                DD_SUBMENU        : 'sek-dropdown-submenu',
                                CARET_TITLE_FLIP  : 'sek-menu-link__row-reverse',
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
                                var _old_direction = _direction == Direction._OPPOSITE ? Direction._DEFAULT : Direction._OPPOSITE;
                                $_dropdown.removeClass( ClassName.OPEN_PREFIX + _old_direction ).addClass( ClassName.OPEN_PREFIX + _direction );
                                if ( $_el.hasClass( ClassName.DD_SUBMENU ) ) {
                                      _caret_title_maybe_flip( $_el, _direction, _old_direction );
                                      _caret_title_maybe_flip( $_dropdown.children( '.' + ClassName.DD_SUBMENU ), _direction, _old_direction );
                                }
                          };
                      if ( $_dropdown.parent().closest( '.'+ClassName.DROPDOWN ).hasClass( ClassName.OPEN_PREFIX + Direction._OPPOSITE ) ) {
                            _setOpenDirection( Direction._OPPOSITE );
                      } else {
                            _setOpenDirection( Direction._DEFAULT );
                      }
                      if ( $_dropdown.offset().left + $_dropdown.width() > $(window).width() ) {
                            _setOpenDirection( 'left' );
                      } else if ( $_dropdown.offset().left < 0 ) {
                            _setOpenDirection( 'right' );
                      }
                }//_maybe_move
          };//dropdownPlacement
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
          $(document).on( Event.CLICK_DATA_API, Selector.DATA_TOGGLE, function (event) {
                if (event.currentTarget.tagName === 'A') {
                      event.preventDefault();
                }

                var $toggler             = $(this),
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
                                  _onSlidingCompleteResetCSS( $collapsible );
                            }
                      });//end slideUp/slideDown
                });//end each
          });//end document bind
    };


    Dropdown();
    SimpleCollapse();
    $( document )
          .on( 'mouseenter', '.sek-nav-toggler', function(){ $(this).addClass( 'hovering' ); } )
          .on( 'mouseleave', '.sek-nav-toggler', function(){ $(this).removeClass( 'hovering' ); } )
          .on( 'show.sek.sekCollapse hide.sek.sekCollapse', '.sek-nav-collapse', function() {
                $('[data-target=#'+$(this).attr('id')+']').removeClass( 'hovering' );
                $(window).trigger('scroll');
          });
});
