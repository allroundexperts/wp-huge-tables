function atctp_show_details(id,url){
    //document.getElementById(id).style.display = document.getElementById(id).style.display === 'none' ? 'table-row':'none';
    $(`#${id} .atctp-collapsible .atctp-collapsible-image`).html(`<img src='${url}'/>`);
    $(`#${id}`).toggle();
}

function atctp_show_all(images){
    const data = JSON.parse(images);
    if(data.length > 0){
        const { id = '0' } = data[0];
        const state = $(`#atctp-${id}`).is(':hidden');
        if(state){
            $('#atctp-toggle').val('Collapse All');
        }else{
            $('#atctp-toggle').val('Expand All');
        }    

    }

    data.map(image => {
        const { id, link } = image;
        return atctp_show_details(`atctp-${id}`, link);
    });
}

new SlimSelect({select: '#slim-select', placeholder: 'Select Media Type(s)'});