App.Components.ImportResultsModal = (function () {
    //const $importResults = $('.import-results-modal');
    const $content = $('.import-results-content');
    const $itemTmpl = $content.find('.import-result-item').clone();
    
    let importResult = null;

    function getResultsItemDataHtml(group, type) {
        const $item = $itemTmpl.clone();
        let arr = importResult[group];

        if (arr == undefined) return null;
        if ( (!arr) | (arr.length == 0)) return null;

        if (group == 'messages') {
            arr = App.Utils.Array.deduplicate(arr);
            titleHtml = lang('import_messages') + ': ' + arr.length;
        } else {
            
            if (!type) {
                type = importResult.type;
            }

            if (group == type) {
                group = 'imported';
            }
            
            titleHtml = lang('import_type_' + type) + ' ' + lang('import_' + group) + ': ' + arr.length;
        }

        $item.find('h6').html(titleHtml);
        $data = $item.find('.import-result-data');
        if (arr.length > 0) {
            $data.empty();
            arr.forEach(element => {
                if (Array.isArray(element)) {
                    element = element.join('\t');
                } else {
                    element = JSON.stringify(element);
                }
                $('<pre/>', { html: element }).appendTo($data);
            });
        } else {
            $item.find('.import-result-item-lines').remove();
        }
        return $item.appendTo($content);
    }

    function setImportResult(result) {
        importResult = result;
        
        $content.empty();
        getResultsItemDataHtml('imported');
        getResultsItemDataHtml('skipped');
        getResultsItemDataHtml('failed');

        getResultsItemDataHtml('customers','customers');
        
        getResultsItemDataHtml('messages');
        
    }

    /**
     * Initialize the module.
     */
    function initialize() {
        //addEventListeners();
    }

    document.addEventListener('DOMContentLoaded', initialize);

    return {
        setImportResult
        //resetModal,
        //validateAppointmentForm,
    };
})();