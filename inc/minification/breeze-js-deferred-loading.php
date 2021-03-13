<?php

if ( ! defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
 * When minification is not enabled but there are scripts into deferred loading option
 * that need to be handles.
 *
 * Class Breeze_Js_Deferred_Loading
 *
 * @since 1.1.8
 */
class Breeze_Js_Deferred_Loading extends Breeze_MinificationBase {
    /**
     * Javascript URLs that need the defer tag.
     *
     * @var array
     *
     * @since 1.1.8
     */
    private $defer_js = [];

    /**
     * Will hold the JS Scripts found in the header
     *
     * @var array
     *
     * @since 1.1.8
     */
    private $head_scripts = [];

    /**
     * Will hold the JS Scripts found in the body/footer
     *
     * @var array
     *
     * @since 1.1.8
     */
    private $footer_scripts = [];

    /**
     * Will hold scripts that need to be removed.
     *
     * @var array
     *
     * @since 1.1.8
     */
    private $jsremovables = [];

    /**
     * Holds scripts that need to be moved to the footer.
     *
     * @var array
     *
     * @since 1.1.8
     */
    private $move_to_footer_js = [];

    /**
     * Prepared scripts that need to be moved to footer.
     *
     * @var array
     *
     * @since 1.1.8
     */
    private $move_to_footer = [];

    /**
     * Files first or last.
     *
     * @var array[]
     *
     * @since 1.1.8
     */
    private $move = [
        'first' => [],
        'last' => [],
    ];

    private $domove = [
        'gaJsHost',
        'load_cmc',
        'jd.gallery.transitions.js',
        'swfobject.embedSWF(',
        'tiny_mce.js',
        'tinyMCEPreInit.go'
    ];

    private $domovelast = [
        'addthis.com',
        '/afsonline/show_afs_search.js',
        'disqus.js',
        'networkedblogs.com/getnetworkwidget',
        'infolinks.com/js/',
        'jd.gallery.js.php',
        'jd.gallery.transitions.js',
        'swfobject.embedSWF(',
        'linkwithin.com/widget.js',
        'tiny_mce.js',
        'tinyMCEPreInit.go',
    ];

    private $dontmove = [
        'gtag',
        'document.write',
        'html5.js',
        'show_ads.js',
        'google_ad',
        'blogcatalog.com/w',
        'tweetmeme.com/i',
        'mybloglog.com/',
        'histats.com/js',
        'ads.smowtion.com/ad.js',
        'statcounter.com/counter/counter.js',
        'widgets.amung.us',
        'ws.amazon.com/widgets',
        'media.fastclick.net',
        '/ads/',
        'comment-form-quicktags/quicktags.php',
        'edToolbar',
        'intensedebate.com',
        'scripts.chitika.net/',
        '_gaq.push',
        'jotform.com/',
        'admin-bar.min.js',
        'GoogleAnalyticsObject',
        'plupload.full.min.js',
        'syntaxhighlighter',
        'adsbygoogle',
        'gist.github.com',
        '_stq',
        'nonce',
        'post_id',
        'data-noptimize',
    ];

    /**
     * Reads the page content and fetches the JavaScript script tags.
     *
     * @param array $options Script options.
     *
     * @return bool
     *
     * @since 1.1.8
     */
    public function read($options = []) {
        // Read the list of scripts that need defer tag.
        if ( ! empty($options['defer_js'])) {
            $this->defer_js = $options['defer_js'];
        }

        // JS files will move to footer
        if ( ! empty($options['move_to_footer_js'])) {
            $this->move_to_footer_js = $options['move_to_footer_js'];
        }

        // is there JS we should simply remove
        $removableJS = apply_filters('breeze_filter_js_removables', '');
        if ( ! empty($removableJS)) {
            $this->jsremovables = array_filter(array_map('trim', explode(',', $removableJS)));
        }

        // noptimize me
        $this->content = $this->hide_noptimize($this->content);
        // Save IE hacks
        $this->content = $this->hide_iehacks($this->content);
        // comments
        $this->content = $this->hide_comments($this->content);

        //Get script files
        $split_content = explode('</head>', $this->content, 2);
        $this->fetch_javascript($split_content[0]);
        $this->fetch_javascript($split_content[1], false);

        if ( ! empty($this->head_scripts) || ! empty($this->footer_scripts)) {
            // Re-order moving to footer JS files
            $ordered_moving_js = array_intersect_key($this->move_to_footer_js, $this->move_to_footer);
            $ordered_moving_js = array_map([$this, 'getpath'], $ordered_moving_js);
            $this->footer_scripts = array_merge($ordered_moving_js, $this->footer_scripts);

            // JS Scripts found, wen can start processing them.
            return true;
        }

        // The page holds no JS scripts
        return false;
    }

    /**
     * Needed function to match Breeze_MinificationBase class pattern
     *
     * @since 1.1.8
     */
    public function minify() {
        return true;
    }

    /**
     * Needed function to match Breeze_MinificationBase class pattern
     *
     * @since 1.1.8
     */
    public function cache() {
        return true;
    }

    /**
     * Needed function to match Breeze_MinificationBase class pattern
     *
     * @since 1.1.8
     */
    public function getcontent() {
        // Load inline JS to html
        if ( ! empty($this->head_scripts)) {
            $replaceTag = ['</head>', 'before'];
            $js_head = [];

            foreach ($this->head_scripts as $js_url => $js_path) {
                $defer = '';
                if (gettype($js_url) == 'string' && in_array($js_url, $this->defer_js)) {
                    $defer = 'defer ';
                }

                $js_head[] = "<script type='application/javascript' {$defer}src='{$js_url}'></script>\n";
            }
            $js_replacement = '';
            $js_replacement .= implode('', $js_head);
            $this->inject_in_html($js_replacement, $replaceTag);
        }

        if ( ! empty($this->footer_scripts)) {
            $replaceTag = ['</body>', 'before'];
            $js_footer = [];

            foreach ($this->footer_scripts as $js_url => $js_path) {
                $defer = '';
                if (gettype($js_url) == 'string' && in_array($js_url, $this->defer_js)) {
                    $defer = 'defer ';
                }

                $js_footer[] = "<script type='application/javascript' {$defer}src='{$js_url}'></script>\n";
            }
            $js_replacement = '';
            $js_replacement .= implode('', $js_footer);
            $this->inject_in_html($js_replacement, $replaceTag);
        }

        // restore comments
        $this->content = $this->restore_comments($this->content);

        // Restore IE hacks
        $this->content = $this->restore_iehacks($this->content);

        // Restore noptimize
        $this->content = $this->restore_noptimize($this->content);

        return $this->content;
    }

    /**
     * Determines whether a <script> $tag should be aggregated or not.
     *
     * We consider these as "aggregation-safe" currently:
     * - script tags without a `type` attribute
     * - script tags with an explicit `type` of `text/javascript`, 'text/ecmascript',
     *   'application/javascript' or 'application/ecmascript'
     *
     * Everything else should return false.
     *
     * @param string $tag
     *
     * @return bool
     *
     * original function by https://github.com/zytzagoo/ on his AO fork, thanks Tomas!
     */
    public function should_aggregate($tag) {
        preg_match('#<(script[^>]*)>#i', $tag, $scripttag);
        if (strpos($scripttag[1], 'type=') === false) {
            return true;
        }
        if (preg_match('/type=["\']?(?:text|application)\/(?:javascript|ecmascript)["\']?/i', $scripttag[1])) {
            return true;
        }
        return false;
    }

    /**
     * Returns the found javascript
     *
     * @param string $content HTML content
     * @param bool   $head    to process header or not.
     *
     * @return bool
     *
     * @since 1.1.8
     */
    private function fetch_javascript($content = '', $head = true) {
        if (preg_match_all('#<script.*</script>#Usmi', $content, $matches)) {
            foreach ($matches[0] as $tag) {
                // only consider aggregation whitelisted in should_aggregate-function
                if ( ! $this->should_aggregate($tag)) {
                    $tag = '';
                    continue;
                }

                // handle only the scripts that have the a file as source.
                if (preg_match('/\ssrc=("|\')?(.*(\ |\>))("|\')?/Usmi', $tag, $source)) {
                    $source[2] = substr($source[2], 0, -1);
                    if ($this->isremovable($tag, $this->jsremovables)) {
                        $content = str_replace($tag, '', $content);
                        continue;
                    }

                    // External script
                    $url = current(explode('?', $source[2], 2));
                    if ($url[0] == "'" || $url[0] == '"') {
                        $url = substr($url, 1);
                    }
                    if ($url[strlen($url) - 1] == '"' || $url[strlen($url) - 1] == "'") {
                        $url = substr($url, 0, -1);
                    }

                    $path = $this->getpath($url);
                    if ($path !== false && preg_match('#\.js$#', $path)) {
                        if ($this->is_merge_valid($tag)) {
                            //We can merge it
                            if ($head === true) {
                                // If this file will be move to footer
                                if (in_array($url, $this->move_to_footer_js)) {
                                    $this->move_to_footer[$url] = $path;
                                } else {
                                    $this->head_scripts[$url] = $path;
                                }
                            } else {
                                $this->footer_scripts[$url] = $path;
                            }
                        } else {
                            //No merge, but maybe we can move it
                            if ($this->is_movable($tag)) {
                                //Yeah, move it
                                if ($this->move_to_last($tag)) {
                                    $this->move['last'][] = $tag;
                                } else {
                                    $this->move['first'][] = $tag;
                                }
                            } else {
                                //We shouldn't touch this
                                $tag = '';
                            }
                        }
                    } else {
                        if (breeze_validate_url_via_regexp($url)) {
                            if ($head === true) {
                                if (in_array($url, $this->move_to_footer_js)) {
                                    $this->move_to_footer[$url] = $url;
                                } else {
                                    $this->head_scripts[$url] = $url;
                                }
                            } else {
                                $this->footer_scripts[$url] = $url;
                            }
                        }
                    }
                    //Remove the original script tag
                    $content = str_replace($tag, '', $content);
                }
            }
        }

        if ($head === true) {
            $this->content = $content;
        } else {
            $this->content .= '</head>' . $content;
        }

        return true;
    }

    // Checks against the white- and blacklists
    private function is_merge_valid($tag) {
        if ( ! empty($this->whitelist)) {
            foreach ($this->whitelist as $match) {
                if (strpos($tag, $match) !== false) {
                    return true;
                }
            }

            // no match with whitelist
            return false;
        }
        foreach ($this->domove as $match) {
            if (strpos($tag, $match) !== false) {
                //Matched something
                return false;
            }
        }

        if ($this->move_to_last($tag)) {
            return false;
        }

        foreach ($this->dontmove as $match) {
            if (strpos($tag, $match) !== false) {
                //Matched something
                return false;
            }
        }

        // If we're here it's safe to merge
        return true;
    }

    /**
     * Check if the script can be moved
     *
     * @param $tag
     *
     * @return bool
     *
     * @since 1.1.8
     */
    private function is_movable($tag) {
        foreach ($this->domove as $match) {
            if (strpos($tag, $match) !== false) {
                //Matched something
                return true;
            }
        }

        if ($this->move_to_last($tag)) {
            return true;
        }

        foreach ($this->dontmove as $match) {
            if (strpos($tag, $match) !== false) {
                //Matched something
                return false;
            }
        }

        //If we're here it's safe to move
        return true;
    }

    /**
     * Move the script last
     *
     * @param $tag
     *
     * @return bool
     *
     * @since 1.1.8
     */
    private function move_to_last($tag) {
        foreach ($this->domovelast as $match) {
            if (strpos($tag, $match) !== false) {
                //Matched, return true
                return true;
            }
        }

        //Should be in 'first'
        return false;
    }
}
