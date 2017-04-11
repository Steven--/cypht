<?php

/**
 * PGP modules
 * @package modules
 * @subpackage pgp
 */

if (!defined('DEBUG_MODE')) { die(); }


/**
 * @subpackage pgp/handler
 */
class Hm_Handler_load_pgp_data extends Hm_Handler_Module {
    public function process() {
        $headers = $this->get('http_headers');
        $key_servers = array('https://pgp.mit.edu');
        $key_servers = implode(' ', $key_servers);
        $headers['Content-Security-Policy'] = str_replace('connect-src', 'connect-src '.$key_servers, $headers['Content-Security-Policy']);
        $this->out('http_headers', $headers);
    }
}

/**
 * @subpackage pgp/handler
 */
class Hm_Handler_pgp_compose_data extends Hm_Handler_Module {
    public function process() {
        $this->out('html_mail', $this->user_config->get('smtp_compose_type_setting', 0));
        $this->out('pgp_public_keys', $this->user_config->get('pgp_public_keys', array()));
    }
}

/**
 * @subpackage pgp/handler
 */
class Hm_Handler_pgp_message_check extends Hm_Handler_Module {
    public function process() {
        /* TODO: Check for pgp parts, look at current part for pgp lines */
        $pgp = false;
        $struct = $this->get('msg_struct', array());
        $text = $this->get('msg_text');
        if (strpos($text, '----BEGIN PGP MESSAGE-----') !== false) {
            $pgp = true;
        }
        $part_struct = $this->get('msg_struct_current', array());
        if ($part_struct['type'] == 'application' && $part_struct['subtype'] == 'pgp-encrypted') {
            $pgp = true;
        }
        $this->out('pgp_msg_part', $pgp);
    }
}

/**
 * @subpackage pgp/output
 */
class Hm_Output_pgp_compose_controls extends Hm_Output_Module {
    protected function output() {
        if ($this->get('html_mail', 0)) {
            return;
        }
        $pub_keys = $this->get('pgp_public_keys');
        $res = '<script type="text/javascript" src="modules/pgp/assets/openpgp.min.js"></script>'.
            '<div class="pgp_section"><div class="pgp_sign"><label for="pgp_sign">'.$this->trans('PGP Sign').'</label>'.
            '<select id="pgp_sign" size="1"></select></div>';

        if (count($pub_keys) > 0) {
            $res .= '<label for="pgp_encrypt">'.$this->trans('Encrypt for:').
                '</label><select id="pgp_encrypt" size="1">';
            foreach ($pub_keys as $vals) {
                $res .= '<option value="'.$vals[0].'">'.$vals[1].'</option>';
            }
            $res .= '</select>';
        }
        $res .= '</div>';
        return $res;
    }
}

/**
 * @subpackage pgp/output
 */
class Hm_Output_pgp_settings_start extends Hm_Output_Module {
    protected function output() {
        $res = '<div class="pgp_settings"><div class="content_title">'.$this->trans('PGP Settings').'</div>';
        $res .= '<script type="text/javascript" src="modules/pgp/assets/openpgp.min.js"></script>';
        return $res;
    }
}

/**
 * @subpackage pgp/output
 */
class Hm_Output_pgp_settings_public_keys extends Hm_Output_Module {
    protected function output() {
        $res = '<div class="public_title settings_subtitle">'.$this->trans('Public Keys');
        $res .= '<span class="key_count">'.sprintf($this->trans('%s imported'), 0).'</span></div>';
        $res .= '<div class="public_keys pgp_block">';
        $res .= '<div class="pgp_subblock">'.$this->trans('Import a public key from a file').'<br /><br />';
        $res .= '<input id="public_key" type="file"> for <input id="public_email" placeholder="'.$this->trans('E-mail Address');
        $res .= '" type="email"> <input type="button" value="'.$this->trans('Import').'">';
        $res .= '</div><div class="pgp_subblock">'.$this->trans('Or Search a key server for a key to import').'<br /><br />';
        $res .= '<input id="hkp_email" placeholder="'.$this->trans('E-mail Address').'" type="email" /> <select id="hkp_server">';
        $res .= '<option value="https://pgp.mit.edu">https://pgp.mit.edu</option></select> ';
        $res .= '<input type="button" id="hkp_search" value="'.$this->trans('Search').'" />';
        $res .= '<div class="hkp_search_results"></div>';
        $res .= '</div>'.$this->trans('Existing Keys').'<table class="pgp_keys"><thead><tr><th>'.$this->trans('Key').'</th>';
        $res .= '<th>'.$this->trans('E-mail').'</th></tr>';
        $res .= '</thead><tbody></tbody></table>';
        $res .= '</div>';
        return $res;
    }
}

/**
 * @subpackage pgp/output
 */
class Hm_Output_pgp_settings_private_key extends Hm_Output_Module {
    protected function output() {
        $res = '<div class="priv_title settings_subtitle">'.$this->trans('Private Keys');
        $res .= '<span class="key_count">'.sprintf($this->trans('%s imported'), 0).'</span></div>';
        $res .= '<div class="priv_keys pgp_block"><div class="pgp_subblock">';
        $res .= $this->trans('Private keys never leave your browser, and are deleted when you logout');
        $res .= '<br /><br /><input id="priv_key" type="file"> for <input id="priv_email" placeholder="'.$this->trans('E-mail Address');
        $res .= '" type="email"> <input type="button" value="'.$this->trans('Import').'">';
        $res .= '</div>'.$this->trans('Existing Keys').'<table class="pgp_keys"><thead><tr><th>'.$this->trans('Key').'</th>';
        $res .= '<th>'.$this->trans('E-mail').'</th></tr>';
        $res .= '</thead><tbody></tbody></table>';
        $res .= '</div>';
        return $res;
    }
}

/**
 * @subpackage pgp/output
 */
class Hm_Output_pgp_settings_end extends Hm_Output_Module {
    protected function output() {
        return '</div>';
    }
}

/**
 * @subpackage pgp/output
 */
class Hm_Output_pgp_msg_controls extends Hm_Output_Module {
    protected function output() {
        return '<div class="pgp_msg_controls"><select class="pgp_private_keys"></select> <input type="button" class="pgp_btn" value="Decrypt" /></div>';
    }
}

/**
 * @subpackage pgp/output
 */
class Hm_Output_pgp_settings_link extends Hm_Output_Module {
    protected function output() {
        $res = '<li class="menu_profiles"><a class="unread_link" href="?page=pgp">';
        if (!$this->get('hide_folder_icons')) {
            $res .= '<img class="account_icon" src="'.$this->html_safe(Hm_Image_Sources::$lock).'" alt="" width="16" height="16" /> ';
        }
        $res .= $this->trans('PGP').'</a></li>';
        if ($this->format == 'HTML5') {
            return $res;
        }
        $this->concat('formatted_folder_list', $res);
    }
}

