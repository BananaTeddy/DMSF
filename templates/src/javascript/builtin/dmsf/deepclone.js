;window.dmsf.object.deepClone =  function deepClone(objectToClone)
{
    var clonedObject;
    var type = Object.prototype.toString.call(objectToClone);
    var member;
    var memberType;

    if (type === '[object Object]') {
        // our argument was an object so we need to create an object
        clonedObject = {};
        for (member in objectToClone) {
            memberType = Object.prototype.toString.call(objectToClone[member]);
            if (memberType === '[object Object]' || memberType === '[object Array]') {
                // recursively clone on objects and arrays
                clonedObject[member] = deepClone(objectToClone[member]);
            } else {
                clonedObject[member] = objectToClone[member];
            }
        }
    }
    if (type === '[object Array]') {
        // our argument was an array so we need to create an array
        clonedObject = [];
        objectToClone.forEach(function (member) {
            memberType = Object.prototype.toString.call(member);
            if (memberType === '[object Object]' || memberType === '[object Array]') {
                clonedObject.push(deepClone(member));
            } else {
                clonedObject.push(member);
            }
        });
    }

    return clonedObject;
}