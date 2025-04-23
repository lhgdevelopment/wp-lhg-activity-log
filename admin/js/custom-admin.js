let gblelem;
let gblElemType='';
let gblPostIds=[];
let postType='';
jQuery(document).ready(function ($) {
    // Append a full-screen modal to the body
    $('body').append(`
        <div id="custom-popup" style="display: none; z-index: 999999 !important; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5);">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 40px; border-radius: 5px; width: 100%; max-width:600px; text-align: center;">
                <h3 style="margin-top:0px;margin-bottom: 15px;">Please provide additional information before saving the <span id="cust_popup_title">post</span>:</h3>
                <textarea id="additional-info-input" style="width: 100%; height:200px; padding: 5px; margin-bottom: 10px; border:1px solid #cccccc;box-shadow: none;"></textarea>
                <button id="confirm-save" style="margin-right: 5px;background: #2271b1;border-color: #2271b1;color: #fff;text-decoration: none;text-shadow: none;padding: 6px 20px;border-radius: 5px;cursor:pointer;">Save</button>
                <button id="cancel-save" style="margin-right: 5px;background: #b32d2e;border-color: #b32d2e;color: #fff;text-decoration: none;text-shadow: none;padding: 6px 20px;border-radius: 5px;cursor:pointer;">Cancel</button>
            </div>
        </div>
    `);
    // Capture the post / page publish
    $('#publish').on('click', function (event) {
        event.preventDefault(); // Prevent form submission
        const url = new URL(window.location); // Ensure absolute URL
        const urlParams = new URLSearchParams(url.search); 
        if(urlParams.get('post_type')){
            postType = urlParams.get('post_type');
        } else {
            postType = 'post';
        }        
        $('#cust_popup_title').text(postType);
        gblelem = $(this);
        gblElemType = 'submit_' + postType;
        $('#custom-popup').fadeIn(); // Show the popup
    });

    // save draft function
    $('#save-post').on('click', function (event) {
        event.preventDefault(); // Prevent form submission
        const url = new URL(window.location); // Ensure absolute URL
        const urlParams = new URLSearchParams(url.search);

        let postType;

        if (urlParams.has('post_type')) {
            postType = urlParams.get('post_type'); // e.g., 'page' or 'post'
        } else if (urlParams.has('post')) {
            // If it's a post but no post_type param is present, fetch it from the DOM (WordPress admin usually sets it)
            const postId = urlParams.get('post');
            const postTypeElement = document.querySelector('#post_type'); // Hidden input used in WP admin
            if (postTypeElement) {
                postType = postTypeElement.value;
            } else {
                postType = 'post'; // Default fallback
            }
        } else {
            postType = 'post'; // Default fallback
        }       
        $('#cust_popup_title').text(postType);
        gblelem = $(this);
        gblElemType = 'save_draft_' + postType;
        $('#custom-popup').fadeIn(); // Show the popup
    });  

    // post or page trash button
    if (!(window.location.pathname.includes('/wp-admin/users.php'))) {
        $('.submitdelete').on('click', function (event) {
            event.preventDefault(); 
            const url = new URL(window.location); 
            const urlParams = new URLSearchParams(url.search); 
            if(urlParams.get('post_type')){
                postType = urlParams.get('post_type');
            } else {
                postType = 'post';
            }        
            $('#cust_popup_title').text(postType);
            gblelem = $(this);
            gblElemType = 'delete_' + postType;
            $('#custom-popup').fadeIn(); // Show the popup
        });
    } 


    // post or page trash tab in restore button button
    $('.untrash a').on('click', function (event) {
        event.preventDefault(); // Prevent form submission
        const url = new URL(window.location); // Ensure absolute URL
        const urlParams = new URLSearchParams(url.search); 
        if(urlParams.get('post_type')){
            postType = urlParams.get('post_type');
        } else {
            postType = 'post';
        }        
        $('#cust_popup_title').text(postType);
        gblelem = $(this);
        gblElemType = 'restore_' + postType;
        $('#custom-popup').fadeIn(); // Show the popup
    });
  
    // user delete code
    if (window.location.pathname.includes('/wp-admin/users.php')) {
        if($('#submit').length > 0){
            var inputbtn = $('<input>').attr({
                type: 'button',
                name: 'tmpsubmituser',
                class: $('#submit').attr('class'),
                id: 'tmpsubmituser',
                value: $('#submit').val()
            });
            $('#submit').hide();
            $('#submit').parent().prepend(inputbtn);
            $('#submit').parent().append('<style type="text/css">#submit{display:none !important;}</style>');
            // category create
            $('#tmpsubmituser').on('click', function (event) {                
                $('#cust_popup_title').text('user');
                event.preventDefault();
                gblelem = $('#submit');
                gblElemType = 'delete_user';
                $('#custom-popup').fadeIn(); // Show the popup
            });
        }
    } else {   // post category and tag taxonomy create
        if($('#submit').length > 0){
            var inputbtn = $('<input>').attr({
                type: 'button',
                name: 'tmpsubmit',
                class: $('#submit').attr('class'),
                id: 'tmpsubmit',
                value: $('#submit').val()
            });
            $('#submit').hide();
            $('#submit').parent().prepend(inputbtn);
            $('#submit').parent().append('<style type="text/css">#submit{display:none !important;}</style>');
            // category create
            $('#tmpsubmit').on('click', function (event) {
                const url = new URL(window.location.href); // Get the full URL
                const urlParams = new URLSearchParams(url.search); // Extract query parameters
                const taxonomy = urlParams.get('taxonomy'); // Get 'taxonomy' from URL
                postType = (taxonomy !== '') ? taxonomy : 'category';   
                $('#cust_popup_title').text(postType);
                event.preventDefault();
                gblelem = $('#submit');
                gblElemType = 'submit_' + postType;
                $('#custom-popup').fadeIn(); // Show the popup
            });
        }
    }

    
    $('#createusersub').on('click', function (event) {
        event.preventDefault(); // Prevent form submission         
        $('#cust_popup_title').text('user');
        gblelem = $(this);
        gblElemType = 'create_user';
        $('#custom-popup').fadeIn(); // Show the popup
    });

    // post category and tag taxonomy update
    if($('.edit-tag-actions > input[type=submit]').length > 0){
        var inputbtn = $('<input>').attr({
            type: 'button',
            name: 'tmpsubmitup',
            class: $('.edit-tag-actions > input[type=submit]').attr('class'),
            id: 'tmpsubmitup',
            value: $('.edit-tag-actions > input[type=submit]').val()
        });
        $('.edit-tag-actions > input[type=submit]').hide();
        $('.edit-tag-actions > input[type=submit]').parent().prepend(inputbtn);
        $('#tmpsubmitup').on('click', function (event) {
            const url = new URL(window.location.href); // Get the full URL
            const urlParams = new URLSearchParams(url.search); // Extract query parameters
            const taxonomy = urlParams.get('taxonomy'); // Get 'taxonomy' from URL
            postType = (taxonomy !== '') ? taxonomy : 'category';   
            $('#cust_popup_title').text(postType);
            event.preventDefault();
            gblelem = $('.edit-tag-actions > input[type=submit]');
            gblElemType = 'update_' + postType;
            $('#custom-popup').fadeIn(); // Show the popup
        });
    }
    

    // post category and tag delete 
    $('.delete > a.delete-tag').each(function (index, element) {
        var $original = $(element);
        var inputbtn = $('<a>').attr({
            role: $original.attr('role'),
            'aria-label': $original.attr('aria-label'),
            href: $original.attr('href'),
            class:'tmpdelete'
        }).text('Delete')
          .css('cursor', 'pointer');
          $original.hide();
        $original.parent().prepend(inputbtn);
        $('.tmpdelete').on('click', function (event) {
            const url = new URL(window.location.href); // Get the full URL
            const urlParams = new URLSearchParams(url.search); // Extract query parameters
            const taxonomy = urlParams.get('taxonomy'); // Get 'taxonomy' from URL
            postType = (taxonomy !== '') ? taxonomy : 'category';   
            $('#cust_popup_title').text(postType);
            event.preventDefault();
            gblelem = $(this).siblings('a.delete-tag');
            gblElemType = 'delete_taxonomy_' + postType;

            $('#custom-popup').fadeIn(); // Show the popup
        });
    });

    // post category and tag edit click open -> delete click function
    if($('#delete-link > a').length > 0){
        var inputbtn = $('<a>').attr({
            id: 'tmpeditdelete'
        })
        .text('Delete')
        .css({'color': 'red', 'cursor':'pointer'});
        $('#delete-link > a').hide();
        $('#delete-link > a').parent().append(inputbtn);
        $('#tmpeditdelete').on('click', function (event) {
            const url = new URL(window.location.href); // Get the full URL
            const urlParams = new URLSearchParams(url.search); // Extract query parameters
            const taxonomy = urlParams.get('taxonomy'); // Get 'taxonomy' from URL
            postType = (taxonomy !== '') ? taxonomy : 'category'; 
            $('#cust_popup_title').text(postType);
            event.preventDefault();
            gblelem = $('#delete-link > a');
            gblElemType = 'delete_taxonomy_'  + postType;
            $('#custom-popup').fadeIn(); // Show the popup
        });
    }

    // delete the bulk  
    if (!(window.location.pathname.includes('/wp-admin/users.php'))) {
        $('#doaction').on('click', function (event) {
            event.stopPropagation();
            event.preventDefault();    
            const url = new URL(window.location);
            const urlParams = new URLSearchParams(url.search);
        
            let actionDel = $('#bulk-action-selector-top').val(); // Adjust selector if necessary    
            if (actionDel === 'untrash') {
                // Restore functionality
                postType = urlParams.get('post_type') || 'post';
                gblPostIds = $('input[name="post[]"]:checked').map(function () {
                    return $(this).val();
                }).get();
        
                if (gblPostIds.length > 0) {
                    // Perform restore action here
                    gblElemType = 'bulk_restore_' + postType;
                    $('#cust_popup_title').text('Restore ' + postType);
                    $('#custom-popup').fadeIn();
                    gblelem = $(this);
                } else {
                    alert("Please select at least one post to restore.");
                }
            } else if (actionDel === 'delete' || actionDel === 'trash') {
                // Existing delete functionality
                if (urlParams.get('taxonomy')) {
                    postType = urlParams.get('taxonomy');
                    gblPostIds = $('input[name="delete_tags[]"]:checked').map(function () {
                        return $(this).val();
                    }).get();
                    gblElemType = 'bulk_delete_taxonomy_' + postType;
                } else if (urlParams.get('post_type')) {
                    postType = urlParams.get('post_type');
                    gblPostIds = $('input[name="post[]"]:checked').map(function () {
                        return $(this).val();
                    }).get();
                    gblElemType = 'bulk_delete_' + postType;
                } else {
                    postType = 'post';
                    gblPostIds = $('input[name="post[]"]:checked').map(function () {
                        return $(this).val();
                    }).get();
                    gblElemType = 'bulk_delete_post';
                }    
                if (gblPostIds.length > 0) {
                    $('#cust_popup_title').text(postType);                 
                    $('#custom-popup').fadeIn();       
                    gblelem = $(this);
                } else {
                    alert("Please select at least one post.");
                }
            }
        });
    }


    // Themes dashboard active
    setTimeout(function () {
        if($('.theme-actions > a.button.activate').length > 0){
            var inputbtn = $('<a>').attr({
                class: 'button tempactivate'
            })
            .text('Activate').css('cursor', 'pointer');
            $('.theme-actions > a.button.activate').hide();
            $('.theme-actions > a.button.activate').parent().prepend(inputbtn);
            $('.tempactivate').on('click', function (event) {
                $('#cust_popup_title').text('themes');
                event.preventDefault();
                gblelem = $(this).parent().find('a.button.activate');
                gblElemType = 'active_theme';
                $('#custom-popup').fadeIn(); // Show the popup
            });
        }
    }, 3000);

    setTimeout('applytoinstall()',3000);
    setTimeout('applytodelete()',3000);
    setTimeout('popup_active_them()',3000);

    // Plugin active
    $('.plugin-title .activate > a').on('click', function (event) {
        event.preventDefault(); // Prevent form submission
        $('#cust_popup_title').text('plugin');
        gblelem = $(this);
        gblElemType = 'active_plugin';
        $('#custom-popup').fadeIn(); // Show the popup
    });

    // Plugin deactivate
    $('.plugin-title .deactivate > a').on('click', function (event) {
        event.preventDefault(); // Prevent form submission
        $('#cust_popup_title').text('plugin');
        gblelem = $(this);
        gblElemType = 'deactivate_plugin';
        $('#custom-popup').fadeIn(); // Show the popup
    });

    // Plugin delete
    if($('.delete > a.delete').length > 0){
        var inputbtn = $('<a>').attr({
            'aria-label': $('.delete > a.delete').attr('aria-label'),
            class: 'delplugin'
        })
        .text('Delete').css('cursor', 'pointer');
        $('.delete > a.delete').hide();
        $('.delete > a.delete').parent().prepend(inputbtn);
        $('.delplugin').on('click', function (event) {
            $('#cust_popup_title').text('plugin');
            event.preventDefault();
            gblelem = $(this).parent().find('a.delete');
            gblElemType = 'delete_plugin';
            $('#custom-popup').fadeIn(); // Show the popup
        });
    }

    // active bulk in plugin 
    if (!(window.location.pathname.includes('/wp-admin/users.php'))) {
        $('#doaction').on('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
        
            let actionDel = $('#bulk-action-selector-top').val(); // Get selected action
            let actions = {
                'activate-selected': 'bulk_plugin_active',
                'deactivate-selected': 'bulk_plugin_deactivate',
                'delete-selected': 'bulk_plugin_delete',
                'update-selected': 'bulk_plugin_update',
                'enable-auto-update-selected': 'bulk_plugin_enable_auto_update',
                'disable-auto-update-selected': 'bulk_plugin_disable_auto_update'
            };
        
            if (actions[actionDel]) {
                //alert(`bulk ${actionDel.split('-')[0]}`);
                gblPostIds = $('input[name="checked[]"]:checked').map(function () {
                    return $(this).val();
                }).get();
        
                if (gblPostIds.length > 0) {
                    gblElemType = actions[actionDel];
                    $('#cust_popup_title').text('Plugin');
                    $('#custom-popup').fadeIn();
                    gblelem = $(this);
                } else {
                    alert("Please select at least one post to proceed.");
                }
            }
        });
    }


    // Handle save button click inside the popup
    $('#confirm-save').on('click', function () {
        var additionalInfo = $('#additional-info-input').val().trim();
        var activity_type = gblElemType;
        if (additionalInfo) {
            var input = $('<input>').attr({
                type: 'hidden',
                name: 'additional_info',
                class:'additional_info_cl',
                value: additionalInfo
            });
            var inputElem = $('<input>').attr({
                type: 'hidden',
                name: 'activity_type',
                value: activity_type
            });

            let tmpid = gblelem.attr('id');
            let tmpclass = gblelem.parent().attr('class');
            if(tmpid == 'publish'){
                $('form#post').append(input);            
                $('form#post').append(inputElem);            
                $('#custom-popup').fadeOut(); // Hide the popup
                $('#publish').off('click').trigger('click'); // Trigger the original publish action
            } else if(tmpid == 'submit' && !(window.location.pathname.includes('/wp-admin/users.php'))){
                $('form#addtag').append(input);            
                $('form#addtag').append(inputElem); 
                $('#custom-popup').fadeOut(); // Hide the popup
                $('#submit').trigger('click'); // Trigger the original publish action
            } else if (tmpclass == 'edit-tag-actions') {
                $('form#edittag').append(input);
                $('form#edittag').append(inputElem);
                $('.edit-tag-actions > input[type=submit]').trigger('click');
            } else if (tmpid == 'createusersub') {
                $('form#createuser').append(input);
                $('form#createuser').append(inputElem);
                $('#custom-popup').fadeOut(); // Hide the popup
                $('#createusersub').off('click').trigger('click'); // Trigger the original publish action
            } else if(tmpid == 'save-post'){
                $('form#post').append(input);            
                $('form#post').append(inputElem);            
                $('#custom-popup').fadeOut(); // Hide the popup
                $('#save-post').off('click').trigger('click'); // Trigger the original publish action
            } else {     
                let postID = 0;
                let nonPost = '';
                let nonPostuser = '';
                if(gblElemType == 'elementor_post'){
                    postID = elementor.config.document.id; 
                } else if(gblElemType == 'bulk_delete_post' || gblElemType == 'bulk_delete_' + postType || gblElemType == 'bulk_restore_' + postType || gblElemType == 'bulk_delete_taxonomy_' + postType){
                    postID = gblPostIds.join(',');
                } else if(gblElemType == 'delete_taxonomy_' + postType){
                    let gbllink;
                    gbllink = gblelem.attr('href');
                    const url = new URL(gbllink, window.location.origin); 
                    const urlParams = new URLSearchParams(url.search);
                    postID = urlParams.get('tag_ID');
                } else if(gblElemType == 'delete_user'){
                    const url = new URL(window.location);
                    const urlParams = new URLSearchParams(url.search);
                    postID = urlParams.get('user');
                    const usersID = [];

                    // Extract multiple users manually
                    urlParams.forEach((value, key) => {
                        if (key.startsWith('users')) {
                            usersID.push(value);
                        }
                    });

                    if (usersID.length > 0) {
                        postID = usersID.join(',');
                        // Multiple users
                        const singleUseBulDel = "bulk_user_delete"; 
                        nonPostuser = 'users ::' + singleUseBulDel + '::' + usersID.join(',') + '::' + url;
                    } else {
                        const singleUseDel = "user_delete"; 
                        nonPostuser = 'user ::' + singleUseDel + '::' + postID + '::' + url;
                    }
                } else if(gblElemType == 'bulk_plugin_active' || gblElemType ==  'bulk_plugin_deactivate' || gblElemType ==  'bulk_plugin_delete' || gblElemType ==  'bulk_plugin_update' || gblElemType == 'bulk_plugin_enable_auto_update' || gblElemType == 'bulk_plugin_disable_auto_update'){ 
                    postID = gblPostIds.join(',');
                } else if(gblElemType == 'active_theme' || gblElemType == 'install_theme' || gblElemType == 'active_theme_popup' || gblElemType == 'delete_theme'){
                    let gbllink;
                    gbllink = gblelem.attr('href');
                    let tmpTitle = gblelem.attr('aria-label');
                    const url = new URL(gbllink, window.location.origin); // Ensure absolute URL
                    const urlParams = new URLSearchParams(url.search); 
                    nonPost = 'theme ::'+urlParams.get('stylesheet')+'::'+tmpTitle+'::'+gbllink;
                } else if(gblElemType == 'active_plugin' || gblElemType == 'deactivate_plugin' || gblElemType == 'delete_plugin'){
                    let gbllink;
                    gbllink = gblelem.attr('href');
                    let tmpTitle = gblelem.attr('aria-label');
                    const url = new URL(gbllink, window.location.origin); // Ensure absolute URL
                    const urlParams = new URLSearchParams(url.search); 
                    nonPost = 'Plugin ::'+urlParams.get('stylesheet')+'::'+tmpTitle+'::'+gbllink;
                } else {
                    let gbllink;
                    gbllink = gblelem.attr('href');
                    const url = new URL(gbllink, window.location.origin); // Ensure absolute URL
                    const urlParams = new URLSearchParams(url.search); 
                    postID = urlParams.get('post');
                }

                var formData = new FormData();
                formData.append('action', 'lhg_activity_save_elementor_log');
                formData.append('post_id', postID);
                formData.append('non_post', nonPost);
                formData.append('admin_user', nonPostuser);
                formData.append('additional_info', additionalInfo);
                formData.append('nonce', customElementorAjax.nonce);    
                formData.append('activity_type', gblElemType); 
                $.ajax({
                    url: customElementorAjax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) { 
                        $('#custom-popup').fadeOut(); 
                        if(gblElemType == 'elementor_post' || gblElemType == 'bulk_delete_' + postType || gblElemType == 'bulk_restore_' + postType || gblElemType == 'bulk_delete_taxonomy_' + postType || gblElemType == 'bulk_plugin_active' || gblElemType == 'bulk_plugin_deactivate' || gblElemType == 'bulk_plugin_delete' ||  gblElemType == 'bulk_plugin_update' || gblElemType == 'bulk_plugin_enable_auto_update' || gblElemType == 'bulk_plugin_disable_auto_update' || gblElemType == 'delete_user'){
                            $(gblelem).off('click').trigger('click'); // Trigger the original publish action
                        } else if (gblElemType == 'delete_plugin'){ 
                            // window.location = $(gblelem).attr('href');
                            $(gblelem).trigger('click');
                        } else {
                            window.location = $(gblelem).attr('href');
                        }
                    },
                    error: function(error) {
                        console.log('Error:', error);
                    }
                });   

            }
        } else {
            $('#additional-info-input').focus();
            $('#additional-info-input').css("border-color", "#b32d2e");
        }
    });

    // Handle cancel button click
    $('#cancel-save').on('click', function () {
        $('#custom-popup').fadeOut(); // Hide the popup
        $('#additional-info-input').val('');
        $('#additional-info-input').css("border-color", "#cccccc");
    });

    function addPromptToElementorButtons() {
        document.querySelectorAll('*[class*="MuiButtonBase-root"]').forEach(el => {
            if(el.innerText == "Publish"){
                $(el).on('click', function(event) {
                    event.preventDefault();
                    // Show the popup
                    $('#custom-popup').fadeIn();
                    gblelem = $(this);
                    gblElemType = 'elementor_post';
                    return false;
                });
            }
        });
    }

    // Use MutationObserver to handle dynamic changes in Elementor UI
    const observer = new MutationObserver(() => {
        addPromptToElementorButtons();
    });

    observer.observe(document.body, { childList: true, subtree: true });
    addPromptToElementorButtons();
    
});


// theme all new theme intall slug "/wp-admin/theme-install.php?browse=popular"
function applytoinstall() { 
    if (jQuery('.theme-actions > a.button.theme-install').length > 0) {
        var inputbtn = jQuery('<a>').attr({class: 'button tempacttheme'}).text('Install').css('cursor', 'pointer');
        jQuery('.theme-actions > a.button.theme-install').hide();
        jQuery('.theme-actions > a.button.theme-install').parent().prepend(inputbtn);

        jQuery('.tempacttheme').on('click', function (event) {
            jQuery('#cust_popup_title').text('install theme');
            event.preventDefault();
            gblelem = jQuery(this).parent().find('a.button.theme-install');
            gblElemType = 'install_theme';
            jQuery('#custom-popup').fadeIn(); // Show the popup
        });
    }
}

//theme popup delete
function applytodelete() {
     if(jQuery('a.button.delete-theme').length > 0){
        var inputbtn = jQuery('<a>').attr({class: 'button tempdeletetheme'}).text('Delete').css({'background': '#b32d2e','color': '#ffffff', 'cursor':'pointer', 'border-color':'#b32d2e !important;'});
        jQuery('a.button.delete-theme').hide();
        jQuery('a.button.delete-theme').parent().append(inputbtn);
        jQuery('.tempdeletetheme').on('click', function (event) {
            jQuery('#cust_popup_title').text('themes');
            event.preventDefault();
            gblelem = jQuery(this).parent().find('a.button.delete-theme');
            gblElemType = 'delete_theme';
            jQuery('#custom-popup').fadeIn(); // Show the popup
        });
    }
}


function popup_active_them(){
    if(jQuery('.inactive-theme > a.button.activate').length > 0){
        var inputbtn = jQuery('<a>').attr({
            class: 'button tempactivatepopup'
        })
        .text('Active').css('cursor', 'pointer');
        jQuery('.inactive-theme > a.button.activate').hide();
        jQuery('.inactive-theme > a.button.activate').parent().prepend(inputbtn);
        jQuery('.tempactivatepopup').on('click', function (event) {
            jQuery('#cust_popup_title').text('themes');
            event.preventDefault();
            gblelem = jQuery(this).parent().find('a.button.activate');
            gblElemType = 'active_theme_popup';
            jQuery('#custom-popup').fadeIn(); // Show the popup
        });
    }
}