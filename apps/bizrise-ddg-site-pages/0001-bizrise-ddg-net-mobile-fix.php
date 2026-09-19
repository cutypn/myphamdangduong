<?php
/**
 * Plugin Name: Bizrise DDG NÉT Mobile Fix
 * Description: Mobile hardening for the NÉT concierge UI: fixes composer sizing, bottom safe-area overlap, oversized greeting, removes reset/trash control and renames the partner CTA.
 * Version: 1.0.0
 * Author: Bizrise Framework
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_NET_Mobile_Fix {
    private const VERSION = '1.0.0';

    public static function boot(): void {
        add_action('wp_head', [__CLASS__, 'css'], 9999);
        add_action('wp_footer', [__CLASS__, 'js'], 9999);
    }

    public static function css(): void {
        if (is_admin()) { return; }
        ?>
        <style id="bizrise-ddg-net-mobile-fix">
        [data-ddg-net-hotfix="1"] {
            box-sizing: border-box !important;
        }

        @media (max-width: 782px) {
            [data-ddg-net-hotfix="1"] {
                max-height: calc(100dvh - 16px) !important;
                height: calc(100dvh - 16px) !important;
                overflow: hidden !important;
                overscroll-behavior: contain !important;
            }

            [data-ddg-net-hotfix="1"] [data-ddg-net-greeting="1"] {
                font-size: 16px !important;
                line-height: 1.58 !important;
                padding: 16px 18px !important;
                margin: 12px 14px 10px !important;
                max-width: calc(100% - 28px) !important;
                overflow-wrap: anywhere !important;
                word-break: normal !important;
            }

            [data-ddg-net-hotfix="1"] [data-ddg-net-composer="1"] {
                display: flex !important;
                grid-template-columns: minmax(0, 1fr) auto !important;
                align-items: flex-end !important;
                gap: 8px !important;
                width: 100% !important;
                min-width: 0 !important;
                padding-left: 10px !important;
                padding-right: 10px !important;
                box-sizing: border-box !important;
            }

            [data-ddg-net-hotfix="1"] [data-ddg-net-input="1"] {
                flex: 1 1 auto !important;
                width: 100% !important;
                min-width: 0 !important;
                max-width: none !important;
                min-height: 52px !important;
                max-height: 96px !important;
                padding: 12px 14px !important;
                font-size: 16px !important;
                line-height: 1.4 !important;
                white-space: normal !important;
                overflow-wrap: normal !important;
                word-break: normal !important;
                box-sizing: border-box !important;
            }

            [data-ddg-net-hotfix="1"] [data-ddg-net-send="1"] {
                flex: 0 0 56px !important;
                width: 56px !important;
                min-width: 56px !important;
                height: 56px !important;
                min-height: 56px !important;
            }

            [data-ddg-net-hotfix="1"] [data-ddg-net-footer="1"] {
                position: relative !important;
                inset: auto !important;
                padding-bottom: max(10px, env(safe-area-inset-bottom)) !important;
                margin-bottom: 0 !important;
                background: #fff !important;
                z-index: 5 !important;
            }

            [data-ddg-net-hotfix="1"] [data-ddg-net-reset="1"] {
                display: none !important;
            }
        }
        </style>
        <?php
    }

    public static function js(): void {
        if (is_admin()) { return; }
        ?>
        <script id="bizrise-ddg-net-mobile-fix-js">
        (function(){
            'use strict';

            function text(el){
                return (el && (el.textContent || el.innerText) || '').replace(/\s+/g, ' ').trim();
            }

            function hasNeedle(el, needle){
                return text(el).toLocaleLowerCase('vi').indexOf(needle.toLocaleLowerCase('vi')) !== -1;
            }

            function likelyPanel(seed){
                var node = seed;
                var best = null;
                while (node && node !== document.body) {
                    var t = text(node);
                    if (t.indexOf('Tư vấn sản phẩm phù hợp') !== -1 && t.indexOf('Kết thúc phiên') !== -1) {
                        best = node;
                    }
                    if (best && (node.getAttribute('role') === 'dialog' || getComputedStyle(node).position === 'fixed')) {
                        return node;
                    }
                    node = node.parentElement;
                }
                return best;
            }

            function markGreeting(panel){
                var all = panel.querySelectorAll('p,div,article,section');
                for (var i = 0; i < all.length; i++) {
                    var t = text(all[i]);
                    if (t.indexOf('Chào bạn, mình là NÉT') === 0 && t.length < 700) {
                        all[i].setAttribute('data-ddg-net-greeting','1');
                        break;
                    }
                }
            }

            function markHeaderAndReset(panel){
                var netNodes = panel.querySelectorAll('h1,h2,h3,strong,b,span,div');
                var header = null;
                for (var i = 0; i < netNodes.length; i++) {
                    var t = text(netNodes[i]);
                    if (t === 'NÉT' || (t.indexOf('NÉT') !== -1 && t.indexOf('Hiểu làn da') !== -1 && t.length < 120)) {
                        header = netNodes[i];
                        while (header && header !== panel) {
                            var buttons = header.querySelectorAll ? header.querySelectorAll('button,[role="button"]') : [];
                            if (buttons.length >= 2) { break; }
                            header = header.parentElement;
                        }
                        break;
                    }
                }
                if (!header || header === panel) { return; }

                var controls = header.querySelectorAll('button,[role="button"]');
                if (controls.length >= 2) {
                    var close = controls[controls.length - 1];
                    for (var j = 0; j < controls.length; j++) {
                        var c = controls[j];
                        var label = ((c.getAttribute('aria-label') || '') + ' ' + (c.getAttribute('title') || '') + ' ' + text(c)).toLocaleLowerCase('vi');
                        var isReset = /x[oó]a|reset|clear|delete|trash|l[aà]m m[oớ]i/.test(label);
                        if (c !== close && (isReset || controls.length === 2)) {
                            c.setAttribute('data-ddg-net-reset','1');
                        }
                    }
                }
            }

            function renameCTA(panel){
                var nodes = panel.querySelectorAll('button,a,[role="button"]');
                for (var i = 0; i < nodes.length; i++) {
                    var t = text(nodes[i]);
                    if (t === 'Nhận báo giá') {
                        if (nodes[i].childElementCount === 0) {
                            nodes[i].textContent = 'Tư vấn đại lý/Affiliate';
                        } else {
                            var changed = false;
                            var walker = document.createTreeWalker(nodes[i], NodeFilter.SHOW_TEXT);
                            var n;
                            while ((n = walker.nextNode())) {
                                if (n.nodeValue && n.nodeValue.trim() === 'Nhận báo giá') {
                                    n.nodeValue = 'Tư vấn đại lý/Affiliate';
                                    changed = true;
                                    break;
                                }
                            }
                            if (!changed) { nodes[i].setAttribute('aria-label','Tư vấn đại lý/Affiliate'); }
                        }
                    }
                }
            }

            function markComposer(panel){
                var field = panel.querySelector('textarea, input[type="text"], [contenteditable="true"]');
                if (!field) { return; }
                field.setAttribute('data-ddg-net-input','1');

                var composer = field.closest('form');
                if (!composer) {
                    composer = field.parentElement;
                    while (composer && composer !== panel) {
                        var button = composer.querySelector('button,[role="button"]');
                        if (button && composer.contains(field)) { break; }
                        composer = composer.parentElement;
                    }
                }
                if (!composer || composer === panel) { return; }
                composer.setAttribute('data-ddg-net-composer','1');

                var buttons = composer.querySelectorAll('button,[role="button"]');
                if (buttons.length) {
                    buttons[buttons.length - 1].setAttribute('data-ddg-net-send','1');
                }
            }

            function markFooter(panel){
                var nodes = panel.querySelectorAll('button,a,div,footer');
                for (var i = 0; i < nodes.length; i++) {
                    if (hasNeedle(nodes[i], 'Kết thúc phiên')) {
                        var footer = nodes[i];
                        while (footer.parentElement && footer.parentElement !== panel) {
                            var pt = text(footer.parentElement);
                            if (pt.indexOf('Kết thúc phiên') !== -1 && pt.length < 250) {
                                footer = footer.parentElement;
                            } else {
                                break;
                            }
                        }
                        footer.setAttribute('data-ddg-net-footer','1');
                        break;
                    }
                }
            }

            function fix(){
                var seeds = document.querySelectorAll('h1,h2,h3,strong,b,span,div');
                var panel = null;
                for (var i = 0; i < seeds.length; i++) {
                    var t = text(seeds[i]);
                    if ((t === 'NÉT' || t.indexOf('Beauty Concierge') !== -1 || t.indexOf('Hiểu làn da. Chọn đúng chăm sóc.') !== -1) && t.length < 220) {
                        panel = likelyPanel(seeds[i]);
                        if (panel) { break; }
                    }
                }
                if (!panel) { return; }

                panel.setAttribute('data-ddg-net-hotfix','1');
                markHeaderAndReset(panel);
                markGreeting(panel);
                renameCTA(panel);
                markComposer(panel);
                markFooter(panel);
            }

            var queued = false;
            function schedule(){
                if (queued) { return; }
                queued = true;
                requestAnimationFrame(function(){ queued = false; fix(); });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', schedule, {once:true});
            } else {
                schedule();
            }

            var observer = new MutationObserver(schedule);
            observer.observe(document.documentElement, {childList:true, subtree:true});
            window.addEventListener('resize', schedule, {passive:true});
            window.addEventListener('orientationchange', schedule, {passive:true});
        })();
        </script>
        <?php
    }
}

Bizrise_DDG_NET_Mobile_Fix::boot();
