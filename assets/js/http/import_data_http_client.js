App.Http.ImportData = (function () {

    function fullUrl(url) {
        return App.Utils.Url.siteUrl('import_data/' + url);
    }

    function upload(system, type, file) {
        const url = fullUrl('upload');

        const data = {

            csrf_token: vars('csrf_token'),
            
            source_system: system,
            type: type,
            data: file,

        };

        return $.post(url, data);
    }

    return {
        upload
    };
})();