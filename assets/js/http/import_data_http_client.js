App.Http.ImportData = (function () {

    function fUrl(url) {
        const furl = App.Utils.Url.siteUrl('import_data/' + url);
        return furl;
    }

    function upload(system, type, file) {
        const url = fUrl('upload');

        const data = {

            csrf_token: vars('csrf_token'),
            
            source_system: system,
            type: type,
            data: file,

        };

        return $.post(url, data);
    }

    /**
     * 
     * @returns Object
     * OBSOLETE
     */ 
    // function getSystems() {
    //     const url = fUrl('get_systems');

    //     const data = {
    //         csrf_token: vars('csrf_token'),
    //     };

    //     return $.post(url, data);
    // }

    return {
        //getSystems
        upload
    };
})();