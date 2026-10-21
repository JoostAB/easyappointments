window.App.Utils.Array = (function () {
    function deduplicate(orgArray) {
        if (!Array.isArray(orgArray)) return null;

        /*
        return orgArray.filter((elm, idx) => {
            return orgArray.findIndex((findElm) => {
                return (findElm === elm);
            }) < idx;
        });
        */
        newArr = [];
        for (let i = 0; i < orgArray.length; i++ ) {
            const val = orgArray[i];
            let idx = newArr.indexOf(val);
            if (idx == -1) {
                newArr.push(val);
            }
        }

        return newArr;
    }

    return {
        deduplicate,
    };
})();