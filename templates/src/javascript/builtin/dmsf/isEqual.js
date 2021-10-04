;window.dmsf.object.isEqual = function isEqual(a, b) {
    var aProps = Object.getOwnPropertyNames(a);
    var bProps = Object.getOwnPropertyNames(b);

    if (aProps.length !== bProps.length) {
        // if amount of object properties is not equal dont even bother to compare them
        return false;
    }

    for (let i = 0; i < aProps.length; i++) {
        var propName = aProps[i];
        var propType = Object.prototype.toString.call(a[propName]);

        if (propType === '[object Object]' ||
            propType === '[object Array]') {
                // handle nested objects/arrays with recursive call
            if (! isEqual(a[propName], b[propName])) {
                return false;
            }
        } else {
            if (! (a.hasOwnProperty(propName) &&
                b.hasOwnProperty(propName))) {
                    // check if both objects have the named property (handles undefined)
                    return false;
            }
            if (a[propName] !== b[propName]) {
                // simple value comparison
                return false;
            }
        }
    }
    // if all checks were passed, we consider the objects equal
    return true;
}