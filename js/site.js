Hm_Ajax = {

    callback: false,

    request: function(args, callback, extra) {
        Hm_Ajax.callback = callback;
        if (extra) {
            for (name in extra) {
                args.push({'name': name, 'value': extra[name]});
            }
        }
        $.post('', args )
        .done(Hm_Ajax.done)
        .fail(Hm_Ajax.fail)
        .always(Hm_Ajax.always);
        return false;
    },

    done: function(res) {
        if (typeof res == 'string' && res.indexOf('<') == 0) {
            Hm_Ajax.fail(res);
        }
        else if (!res) {
            Hm_Ajax.fail(res);
        }
        else {
            res = jQuery.parseJSON(res);
            if (Hm_Ajax.callback) {
                Hm_Ajax.callback(res);
            }
        }
    },

    fail: function(res) {
        Hm_Notices.show({0: 'An error occured communicating with the server'});
        $("input[type='submit']").attr('disabled', false);
    },

    always: function(res) {
    }
}

Hm_Notices = {

    show: function(msgs) {
        var msg_list = $.map(msgs, function(v) { return v; });
        $('.sys_messages').html(msg_list.join('<br />'));
    }
}

Hm_Folders = {

    show: function(folders) {
        var folder_html = '';
        for (folder in folders) {
            folder_html += '<div>'+folders[folder]+'</div>';
        }
        $('.imap_folder_data').html(folder_html);
    }
}
