<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>LearnHub API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=PT+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-elements.style.css") }}" media="screen">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/docco.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>
    <script type="module">
        import {CodeJar} from 'https://medv.io/codejar/codejar.js'
        window.CodeJar = CodeJar;
    </script>

            <script>
            var tryItOutBaseUrl = "http://learnhub.test";
            var useCsrf = Boolean();
            var csrfUrl = "/sanctum/csrf-cookie";
        </script>
        <script src="{{ asset("/vendor/scribe/js/tryitout-4.35.0.js") }}"></script>
        <style>
            .code-editor, .response-content {
                color: whitesmoke;
                background-color: transparent;
            }
            /*
             Problem: we want syntax highlighting for the Try It Out JSON body code editor
             However, the Try It Out area uses a dark background, while request and response samples
             (which are already highlighted) use a light background. HighlightJS can only use one theme per document.
             Our options:
             1. Change the bg of one. => No, it looks out of place on the page.
             2. Use the same highlighting for both. => Nope, one would be unreadable.
             3. Copy styles for a dark-bg h1js theme and prefix them for the CodeEditor, which is what we're doing.
             Since it's only JSON, we only need a few styles anyway.
             Styles taken from the Nord theme: https://github.com/highlightjs/highlight.js/blob/3997c9b430a568d5ad46d96693b90a74fc01ea7f/src/styles/nord.css#L2
             */
            .code-editor > .hljs-attr {
                color: #8FBCBB;
            }
            .code-editor > .hljs-string {
                color: #A3BE8C;
            }
            .code-editor > .hljs-number {
                color: #B48EAD;
            }
            .code-editor > .hljs-literal{
                color: #81A1C1;
            }

        </style>

        <script>
            function tryItOut(btnElement) {
                btnElement.disabled = true;

                let endpointId = btnElement.dataset.endpoint;

                let errorPanel = document.querySelector(`.tryItOut-error[data-endpoint=${endpointId}]`);
                errorPanel.hidden = true;
                let responsePanel = document.querySelector(`.tryItOut-response[data-endpoint=${endpointId}]`);
                responsePanel.hidden = true;

                let form = btnElement.form;
                let { method, path, hasjsonbody: hasJsonBody} = form.dataset;
                let body = {};
                if (hasJsonBody === "1") {
                    body = form.querySelector('.code-editor').textContent;
                } else if (form.dataset.hasfiles === "1") {
                    body = new FormData();
                    form.querySelectorAll('input[data-component=body]')
                        .forEach(el => {
                            if (el.type === 'file') {
                                if (el.files[0]) body.append(el.name, el.files[0])
                            } else body.append(el.name, el.value);
                        });
                } else {
                    form.querySelectorAll('input[data-component=body]').forEach(el => {
                        _.set(body, el.name, el.value);
                    });
                }

                const urlParameters = form.querySelectorAll('input[data-component=url]');
                urlParameters.forEach(el => (path = path.replace(new RegExp(`\\{${el.name}\\??}`), el.value)));

                const headers = Object.fromEntries(Array.from(form.querySelectorAll('input[data-component=header]'))
                    .map(el => [el.name, (el.dataset.prefix || '') + el.value]));

                const query = {}
                form.querySelectorAll('input[data-component=query]').forEach(el => {
                    _.set(query, el.name, el.value);
                });

                let preflightPromise = Promise.resolve();
                if (window.useCsrf && window.csrfUrl) {
                    preflightPromise = makeAPICall('GET', window.csrfUrl).then(() => {
                        headers['X-XSRF-TOKEN'] = getCookie('XSRF-TOKEN');
                    });
                }

                return preflightPromise.then(() => makeAPICall(method, path, body, query, headers, endpointId))
                    .then(([responseStatus, statusText, responseContent, responseHeaders]) => {
                        responsePanel.hidden = false;
                        responsePanel.querySelector(`.response-status`).textContent = responseStatus + " " + statusText ;

                        let contentEl = responsePanel.querySelector(`.response-content`);
                        if (responseContent === '') {
                            contentEl.textContent = contentEl.dataset.emptyResponseText;
                            return;
                        }

                        // Prettify it if it's JSON
                        let isJson = false;
                        try {
                            const jsonParsed = JSON.parse(responseContent);
                            if (jsonParsed !== null) {
                                isJson = true;
                                responseContent = JSON.stringify(jsonParsed, null, 4);
                            }
                        } catch (e) {}

                        contentEl.innerHTML = responseContent;
                        isJson && window.hljs.highlightElement(contentEl);
                    })
                    .catch(err => {
                        console.log(err);
                        let errorMessage = err.message || err;
                        errorPanel.hidden = false;
                        errorPanel.querySelector(`.error-message`).textContent = errorMessage;
                    })
                    .finally(() => { btnElement.disabled = false } );
            }

            window.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.tryItOut-btn').forEach(el => {
                    el.addEventListener('click', () => tryItOut(el));
                });
            })
        </script>
    
</head>

<body>

    <script>
        function switchExampleLanguage(lang) {
            document.querySelectorAll(`.example-request`).forEach(el => el.style.display = 'none');
            document.querySelectorAll(`.example-request-${lang}`).forEach(el => el.style.display = 'initial');
            document.querySelectorAll(`.example-request-lang-toggle`).forEach(el => el.value = lang);
        }
    </script>

<script>
    function switchExampleResponse(endpointId, index) {
        document.querySelectorAll(`.example-response-${endpointId}`).forEach(el => el.style.display = 'none');
        document.querySelectorAll(`.example-response-${endpointId}-${index}`).forEach(el => el.style.display = 'initial');
        document.querySelectorAll(`.example-response-${endpointId}-toggle`).forEach(el => el.value = index);
    }


    /*
     * Requirement: a div with class `expansion-chevrons`
     *   (or `expansion-chevrons-solid` to use the solid version).
     * Also add the `expanded` class if your div is expanded by default.
     */
    function toggleExpansionChevrons(evt) {
        let elem = evt.currentTarget;

        let chevronsArea = elem.querySelector('.expansion-chevrons');
        const solid = chevronsArea.classList.contains('expansion-chevrons-solid');
        const newState = chevronsArea.classList.contains('expanded') ? 'expand' : 'expanded';
        if (newState === 'expanded') {
            const selector = solid ? '#expanded-chevron-solid' : '#expanded-chevron';
            const template = document.querySelector(selector);
            const chevron = template.content.cloneNode(true);
            chevronsArea.replaceChildren(chevron);
            chevronsArea.classList.add('expanded');
        } else {
            const selector = solid ? '#expand-chevron-solid' : '#expand-chevron';
            const template = document.querySelector(selector);
            const chevron = template.content.cloneNode(true);
            chevronsArea.replaceChildren(chevron);
            chevronsArea.classList.remove('expanded');
        }

    }

    /**
     * 1. Make sure the children are inside the parent element
     * 2. Add `expandable` class to the parent
     * 3. Add `children` class to the children.
     * 4. Wrap the default chevron SVG in a div with class `expansion-chevrons`
     *   (or `expansion-chevrons-solid` to use the solid version).
     *   Also add the `expanded` class if your div is expanded by default.
     */
    function toggleElementChildren(evt) {
        let elem = evt.currentTarget;
        let children = elem.querySelector(`.children`);
        if (!children) return;

        if (children.contains(event.target)) return;

        let oldState = children.style.display
        if (oldState === 'none') {
            children.style.removeProperty('display');
            toggleExpansionChevrons(evt);
        } else {
            children.style.display = 'none';
            toggleExpansionChevrons(evt);
        }

        evt.stopPropagation();
    }

    function highlightSidebarItem(evt = null) {
        if (evt && evt.oldURL) {
            let oldHash = new URL(evt.oldURL).hash.slice(1);
            if (oldHash) {
                let previousItem = window['sidebar'].querySelector(`#toc-item-${oldHash}`);
                previousItem.classList.remove('sl-bg-primary-tint');
                previousItem.classList.add('sl-bg-canvas-100');
            }
        }

        let newHash = location.hash.slice(1);
        if (newHash) {
            let item = window['sidebar'].querySelector(`#toc-item-${newHash}`);
            item.classList.remove('sl-bg-canvas-100');
            item.classList.add('sl-bg-primary-tint');
        }
    }

    addEventListener('DOMContentLoaded', () => {
        highlightSidebarItem();

        document.querySelectorAll('.code-editor').forEach(elem => CodeJar(elem, (editor) => {
            // highlight.js does not trim old tags,
            // which means highlighting doesn't update on type (only on paste)
            // See https://github.com/antonmedv/codejar/issues/18
            editor.textContent = editor.textContent
            return hljs.highlightElement(editor)
        }));

        document.querySelectorAll('.expandable').forEach(el => {
            el.addEventListener('click', toggleElementChildren);
        });

        document.querySelectorAll('details').forEach(el => {
            el.addEventListener('toggle', toggleExpansionChevrons);
        });
    });

    addEventListener('hashchange', highlightSidebarItem);
</script>

<div class="sl-elements sl-antialiased sl-h-full sl-text-base sl-font-ui sl-text-body sl-flex sl-inset-0">

    <div id="sidebar" class="sl-flex sl-overflow-y-auto sl-flex-col sl-sticky sl-inset-y-0 sl-pt-8 sl-bg-canvas-100 sl-border-r"
     style="width: calc((100% - 1800px) / 2 + 300px); padding-left: calc((100% - 1800px) / 2); min-width: 300px; max-height: 100vh">
    <div class="sl-flex sl-items-center sl-mb-5 sl-ml-4">
                <h4 class="sl-text-paragraph sl-leading-snug sl-font-prose sl-font-semibold sl-text-heading">
            LearnHub API Documentation
        </h4>
    </div>

    <div class="sl-flex sl-overflow-y-auto sl-flex-col sl-flex-grow sl-flex-shrink">
        <div class="sl-overflow-y-auto sl-w-full sl-bg-canvas-100">
            <div class="sl-my-3">
                                    <div class="expandable">
                        <div title="Introduction" id="toc-item-introduction"
                             class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-4 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none">
                            <a href="#introduction"
                               class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0">Introduction</a>
                                                    </div>

                                            </div>
                                    <div class="expandable">
                        <div title="Authenticating requests" id="toc-item-authenticating-requests"
                             class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-4 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none">
                            <a href="#authenticating-requests"
                               class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0">Authenticating requests</a>
                                                    </div>

                                            </div>
                                    <div class="expandable">
                        <div title="Admin" id="toc-item-admin"
                             class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-4 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none">
                            <a href="#admin"
                               class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0">Admin</a>
                                                            <div class="sl-flex sl-items-center sl-text-xs expansion-chevrons">
                                    <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                         data-icon="chevron-right"
                                         class="svg-inline--fa fa-chevron-right fa-fw sl-icon sl-text-muted"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                        <path fill="currentColor"
                                              d="M96 480c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L242.8 256L73.38 86.63c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l192 192c12.5 12.5 12.5 32.75 0 45.25l-192 192C112.4 476.9 104.2 480 96 480z"></path>
                                    </svg>
                                </div>
                                                    </div>

                                                    <div class="children" style="display: none;">
                                                                    <div class="expandable">
                                        <div class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-8 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none"
                                             id="toc-item-admin-POSTadmin-login">
                                            <div class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0" title="Login">
                                                <a class="ElementsTableOfContentsItem sl-block sl-no-underline"
                                                   href="#admin-POSTadmin-login">
                                                    Login
                                                </a>
                                            </div>
                                                                                    </div>

                                                                            </div>
                                                                    <div class="expandable">
                                        <div class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-8 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none"
                                             id="toc-item-admin-POSTadmin-logout">
                                            <div class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0" title="Logout">
                                                <a class="ElementsTableOfContentsItem sl-block sl-no-underline"
                                                   href="#admin-POSTadmin-logout">
                                                    Logout
                                                </a>
                                            </div>
                                                                                    </div>

                                                                            </div>
                                                            </div>
                                            </div>
                                    <div class="expandable">
                        <div title="General" id="toc-item-general"
                             class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-4 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none">
                            <a href="#general"
                               class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0">General</a>
                                                            <div class="sl-flex sl-items-center sl-text-xs expansion-chevrons">
                                    <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                         data-icon="chevron-right"
                                         class="svg-inline--fa fa-chevron-right fa-fw sl-icon sl-text-muted"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                        <path fill="currentColor"
                                              d="M96 480c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L242.8 256L73.38 86.63c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l192 192c12.5 12.5 12.5 32.75 0 45.25l-192 192C112.4 476.9 104.2 480 96 480z"></path>
                                    </svg>
                                </div>
                                                    </div>

                                                    <div class="children" style="display: none;">
                                                                    <div class="expandable">
                                        <div class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-8 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none"
                                             id="toc-item-general-GET-">
                                            <div class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0" title="GET /">
                                                <a class="ElementsTableOfContentsItem sl-block sl-no-underline"
                                                   href="#general-GET-">
                                                    GET /
                                                </a>
                                            </div>
                                                                                    </div>

                                                                            </div>
                                                                    <div class="expandable">
                                        <div class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-8 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none"
                                             id="toc-item-general-GETsanctum-csrf-cookie">
                                            <div class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0" title="Return an empty response simply to trigger the storage of the CSRF cookie in the browser.">
                                                <a class="ElementsTableOfContentsItem sl-block sl-no-underline"
                                                   href="#general-GETsanctum-csrf-cookie">
                                                    Return an empty response simply to trigger the storage of the CSRF cookie in the browser.
                                                </a>
                                            </div>
                                                                                    </div>

                                                                            </div>
                                                            </div>
                                            </div>
                                    <div class="expandable">
                        <div title="Website" id="toc-item-website"
                             class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-4 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none">
                            <a href="#website"
                               class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0">Website</a>
                                                            <div class="sl-flex sl-items-center sl-text-xs expansion-chevrons">
                                    <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                         data-icon="chevron-right"
                                         class="svg-inline--fa fa-chevron-right fa-fw sl-icon sl-text-muted"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                        <path fill="currentColor"
                                              d="M96 480c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L242.8 256L73.38 86.63c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l192 192c12.5 12.5 12.5 32.75 0 45.25l-192 192C112.4 476.9 104.2 480 96 480z"></path>
                                    </svg>
                                </div>
                                                    </div>

                                                    <div class="children" style="display: none;">
                                                                    <div class="expandable">
                                        <div class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-8 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none"
                                             id="toc-item-website-GETblog-posts">
                                            <div class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0" title="Blog posts">
                                                <a class="ElementsTableOfContentsItem sl-block sl-no-underline"
                                                   href="#website-GETblog-posts">
                                                    Blog posts
                                                </a>
                                            </div>
                                                                                    </div>

                                                                            </div>
                                                                    <div class="expandable">
                                        <div class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-8 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none"
                                             id="toc-item-website-GETblog-posts--slug-">
                                            <div class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0" title="Blog Post">
                                                <a class="ElementsTableOfContentsItem sl-block sl-no-underline"
                                                   href="#website-GETblog-posts--slug-">
                                                    Blog Post
                                                </a>
                                            </div>
                                                                                    </div>

                                                                            </div>
                                                                    <div class="expandable">
                                        <div class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-8 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none"
                                             id="toc-item-website-GETblog-post-tags">
                                            <div class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0" title="Blog Post Tags">
                                                <a class="ElementsTableOfContentsItem sl-block sl-no-underline"
                                                   href="#website-GETblog-post-tags">
                                                    Blog Post Tags
                                                </a>
                                            </div>
                                                                                    </div>

                                                                            </div>
                                                                    <div class="expandable">
                                        <div class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-8 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none"
                                             id="toc-item-website-GETblog-post-tags--tag-">
                                            <div class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0" title="Blog Post By Tag">
                                                <a class="ElementsTableOfContentsItem sl-block sl-no-underline"
                                                   href="#website-GETblog-post-tags--tag-">
                                                    Blog Post By Tag
                                                </a>
                                            </div>
                                                                                    </div>

                                                                            </div>
                                                                    <div class="expandable">
                                        <div class="sl-flex sl-items-center sl-h-md sl-pr-4 sl-pl-8 sl-bg-canvas-100 hover:sl-bg-canvas-200 sl-cursor-pointer sl-select-none"
                                             id="toc-item-website-POSTcontact">
                                            <div class="sl-flex-1 sl-items-center sl-truncate sl-mr-1.5 sl-p-0" title="Contact">
                                                <a class="ElementsTableOfContentsItem sl-block sl-no-underline"
                                                   href="#website-POSTcontact">
                                                    Contact
                                                </a>
                                            </div>
                                                                                    </div>

                                                                            </div>
                                                            </div>
                                            </div>
                            </div>

        </div>
        <div class="sl-flex sl-items-center sl-px-4 sl-py-3 sl-border-t">
            Last updated: April 17, 2024
        </div>

        <div class="sl-flex sl-items-center sl-px-4 sl-py-3 sl-border-t">
            <a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a>
        </div>
    </div>
</div>

    <div class="sl-overflow-y-auto sl-flex-1 sl-w-full sl-px-16 sl-bg-canvas sl-py-16" style="max-width: 1500px;">

        <div class="sl-mb-10">
            <div class="sl-mb-4">
                <h1 class="sl-text-5xl sl-leading-tight sl-font-prose sl-font-semibold sl-text-heading">
                    LearnHub API Documentation
                </h1>
                                    <a title="Download Postman collection" class="sl-mx-1"
                       href="{{ route("scribe.postman") }}" target="_blank">
                        <small>Postman collection →</small>
                    </a>
                                                    <a title="Download OpenAPI spec" class="sl-mx-1"
                       href="{{ route("scribe.openapi") }}" target="_blank">
                        <small>OpenAPI spec →</small>
                    </a>
                            </div>

            <div class="sl-prose sl-markdown-viewer sl-my-4">
                <h1 id="introduction">Introduction</h1>
<p>The documentation</p>
<aside>
    <strong>Base URL</strong>: <code>http://learnhub.test</code>
</aside>
<p>This documentation aims to provide all the information you need to work with our API.</p>
<aside>As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).</aside>

                <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>
            </div>
        </div>

        <h1 id="admin"
        class="sl-text-5xl sl-leading-tight sl-font-prose sl-text-heading"
    >
        Admin
    </h1>

    

                                <div class="sl-stack sl-stack--vertical sl-stack--8 HttpOperation sl-flex sl-flex-col sl-items-stretch sl-w-full">
    <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
        <div class="sl-relative">
            <div class="sl-stack sl-stack--horizontal sl-stack--5 sl-flex sl-flex-row sl-items-center">
                <h2 class="sl-text-3xl sl-leading-tight sl-font-prose sl-text-heading sl-mt-5 sl-mb-1"
                    id="admin-POSTadmin-login">
                    Login
                </h2>
            </div>
        </div>

        <div class="sl-relative">
            <div title="http://learnhub.test/admin/login"
                     class="sl-stack sl-stack--horizontal sl-stack--3 sl-inline-flex sl-flex-row sl-items-center sl-max-w-full sl-font-mono sl-py-2 sl-pr-4 sl-bg-canvas-50 sl-rounded-lg"
                >
                                            <div class="sl-text-lg sl-font-semibold sl-px-2.5 sl-py-1 sl-text-on-primary sl-rounded-lg"
                             style="background-color: black;"
                        >
                            POST
                        </div>
                                        <div class="sl-flex sl-overflow-x-hidden sl-text-lg sl-select-all">
                        <div dir="rtl"
                             class="sl-overflow-x-hidden sl-truncate sl-text-muted">http://learnhub.test</div>
                        <div class="sl-flex-1 sl-font-semibold">/admin/login</div>
                    </div>

                                    </div>
        </div>

        <p>This endpoint enables users with admin role to sign in</p>
    </div>
    <div class="sl-flex">
        <div data-testid="two-column-left" class="sl-flex-1 sl-w-0">
            <div class="sl-stack sl-stack--vertical sl-stack--10 sl-flex sl-flex-col sl-items-stretch">
                <div class="sl-stack sl-stack--vertical sl-stack--8 sl-flex sl-flex-col sl-items-stretch">
                                            <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">
                                Headers
                            </h3>
                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Content-Type</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Accept</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    
                    

                    
                                            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">Body Parameters</h3>

                                <div class="sl-text-sm">
                                    <div class="expandable sl-text-sm sl-border-l sl-ml-px">
        <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">email</div>
                                            <span class="sl-truncate sl-text-muted">password</span>
                                    </div>
                                    <div class="sl-flex-1 sl-h-px sl-mx-3"></div>
                    <span class="sl-ml-2 sl-text-warning">required</span>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        hkautzer@example.com
                    </div>
                </div>
            </div>
            </div>
</div>

            </div>
    <div class="expandable sl-text-sm sl-border-l sl-ml-px">
        <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">password</div>
                                            <span class="sl-truncate sl-text-muted">string</span>
                                    </div>
                                    <div class="sl-flex-1 sl-h-px sl-mx-3"></div>
                    <span class="sl-ml-2 sl-text-warning">required</span>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        quia
                    </div>
                </div>
            </div>
            </div>
</div>

            </div>
                            </div>
                        </div>
                    
                                    </div>
            </div>
        </div>

        <div data-testid="two-column-right" class="sl-relative sl-w-2/5 sl-ml-16" style="max-width: 500px;">
            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">

                                    <div class="sl-inverted">
    <div class="sl-overflow-y-hidden sl-rounded-lg">
        <form class="TryItPanel sl-bg-canvas-100 sl-rounded-lg"
              data-method="POST"
              data-path="admin/login"
              data-hasfiles="0"
              data-hasjsonbody="1">
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Headers
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                                                            <label aria-hidden="true"
                                       for="header-POSTadmin-login-Content-Type">Content-Type</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Content-Type" name="Content-Type"
                                               id="header-POSTadmin-login-Content-Type"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                                                            <label aria-hidden="true"
                                       for="header-POSTadmin-login-Accept">Accept</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Accept" name="Accept"
                                               id="header-POSTadmin-login-Accept"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
            
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Body
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                                                    <div class="TextRequestBody sl-p-4">
                                <div class="code-editor language-json"
                                     id="json-body-POSTadmin-login"
                                     style="font-family: var(--font-code); font-size: 12px; line-height: var(--lh-code);"
                                >{
    "email": "hkautzer@example.com",
    "password": "quia"
}</div>
                            </div>
                                            </div>
                </div>
            
            <div class="SendButtonHolder sl-mt-4 sl-p-4 sl-pt-0">
                <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-center">
                    <button type="button" data-endpoint="POSTadmin-login"
                            class="tryItOut-btn sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-bg-primary hover:sl-bg-primary-dark active:sl-bg-primary-darker disabled:sl-bg-canvas-100 sl-text-on-primary disabled:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70"
                    >
                        Send Request 💥
                    </button>
                </div>
            </div>

            <div data-endpoint="POSTadmin-login"
                 class="tryItOut-error expandable sl-panel sl-outline-none sl-w-full" hidden>
                <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                     role="button">
                    <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                        <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                 data-icon="caret-down"
                                 class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                            </svg>
                        </div>
                        Request failed with error
                    </div>
                </div>
                <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                    <div class="sl-panel__content sl-p-4">
                        <p class="sl-pb-2"><strong class="error-message"></strong></p>
                        <p class="sl-pb-2">Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</p>
                    </div>
                </div>
            </div>

                <div data-endpoint="POSTadmin-login"
                     class="tryItOut-response expandable sl-panel sl-outline-none sl-w-full" hidden>
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Received response
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="sl-panel__content sl-p-4">
                            <p class="sl-pb-2 response-status"></p>
                            <pre><code class="sl-pb-2 response-content language-json"
                                       data-empty-response-text="<Empty response>"
                                       style="max-height: 300px;"></code></pre>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
                
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                                    <div class="sl--ml-2">
                                        Example request:
                                        <select class="example-request-lang-toggle sl-text-base"
                                                aria-label="Request Sample Language"
                                                onchange="switchExampleLanguage(event.target.value);">
                                                                                            <option>bash</option>
                                                                                            <option>javascript</option>
                                                                                            <option>php</option>
                                                                                            <option>python</option>
                                                                                    </select>
                                    </div>
                                </div>
                            </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-bash"
                                     style="">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-bash">curl --request POST \
    "http://learnhub.test/admin/login" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"hkautzer@example.com\",
    \"password\": \"quia\"
}"
</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-javascript"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-javascript">const url = new URL(
    "http://learnhub.test/admin/login"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "hkautzer@example.com",
    "password": "quia"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-php"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://learnhub.test/admin/login';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'email' =&gt; 'hkautzer@example.com',
            'password' =&gt; 'quia',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-python"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-python">import requests
import json

url = 'http://learnhub.test/admin/login'
payload = {
    "email": "hkautzer@example.com",
    "password": "quia"
}
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre>                                        </div>
                                    </div>
                                </div>
                                                    </div>
                    
                            </div>
    </div>
</div>

                    <div class="sl-stack sl-stack--vertical sl-stack--8 HttpOperation sl-flex sl-flex-col sl-items-stretch sl-w-full">
    <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
        <div class="sl-relative">
            <div class="sl-stack sl-stack--horizontal sl-stack--5 sl-flex sl-flex-row sl-items-center">
                <h2 class="sl-text-3xl sl-leading-tight sl-font-prose sl-text-heading sl-mt-5 sl-mb-1"
                    id="admin-POSTadmin-logout">
                    Logout
                </h2>
            </div>
        </div>

        <div class="sl-relative">
            <div title="http://learnhub.test/admin/logout"
                     class="sl-stack sl-stack--horizontal sl-stack--3 sl-inline-flex sl-flex-row sl-items-center sl-max-w-full sl-font-mono sl-py-2 sl-pr-4 sl-bg-canvas-50 sl-rounded-lg"
                >
                                            <div class="sl-text-lg sl-font-semibold sl-px-2.5 sl-py-1 sl-text-on-primary sl-rounded-lg"
                             style="background-color: black;"
                        >
                            POST
                        </div>
                                        <div class="sl-flex sl-overflow-x-hidden sl-text-lg sl-select-all">
                        <div dir="rtl"
                             class="sl-overflow-x-hidden sl-truncate sl-text-muted">http://learnhub.test</div>
                        <div class="sl-flex-1 sl-font-semibold">/admin/logout</div>
                    </div>

                                                    <div class="sl-font-prose sl-font-semibold sl-px-1.5 sl-py-0.5 sl-text-on-primary sl-rounded-lg"
                                 style="background-color: darkred"
                            >requires authentication
                            </div>
                                    </div>
        </div>

        <p>This endpoint enables users with admin role to log out</p>
    </div>
    <div class="sl-flex">
        <div data-testid="two-column-left" class="sl-flex-1 sl-w-0">
            <div class="sl-stack sl-stack--vertical sl-stack--10 sl-flex sl-flex-col sl-items-stretch">
                <div class="sl-stack sl-stack--vertical sl-stack--8 sl-flex sl-flex-col sl-items-stretch">
                                            <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">
                                Headers
                            </h3>
                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Content-Type</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Accept</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    
                    

                    
                    
                                    </div>
            </div>
        </div>

        <div data-testid="two-column-right" class="sl-relative sl-w-2/5 sl-ml-16" style="max-width: 500px;">
            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">

                                    <div class="sl-inverted">
    <div class="sl-overflow-y-hidden sl-rounded-lg">
        <form class="TryItPanel sl-bg-canvas-100 sl-rounded-lg"
              data-method="POST"
              data-path="admin/logout"
              data-hasfiles="0"
              data-hasjsonbody="0">
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Auth
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                            <label aria-hidden="true"
                                   for="auth-POSTadmin-logout">Authorization</label>
                            <span class="sl-mx-3">:</span>
                            <div class="sl-flex sl-flex-1">
                                <div class="sl-input sl-flex-1 sl-relative">
                                    <code>Bearer </code>
                                    <input aria-label="Authorization"
                                           id="auth-POSTadmin-logout"
                                           data-component="header"
                                           data-prefix="Bearer "
                                           name="Authorization"
                                           placeholder="{YOUR_AUTH_KEY}"
                                           class="auth-value sl-relative sl-w-3/5 sl-h-md sl-text-base sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Headers
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                                                            <label aria-hidden="true"
                                       for="header-POSTadmin-logout-Content-Type">Content-Type</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Content-Type" name="Content-Type"
                                               id="header-POSTadmin-logout-Content-Type"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                                                            <label aria-hidden="true"
                                       for="header-POSTadmin-logout-Accept">Accept</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Accept" name="Accept"
                                               id="header-POSTadmin-logout-Accept"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
            
            
            
            <div class="SendButtonHolder sl-mt-4 sl-p-4 sl-pt-0">
                <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-center">
                    <button type="button" data-endpoint="POSTadmin-logout"
                            class="tryItOut-btn sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-bg-primary hover:sl-bg-primary-dark active:sl-bg-primary-darker disabled:sl-bg-canvas-100 sl-text-on-primary disabled:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70"
                    >
                        Send Request 💥
                    </button>
                </div>
            </div>

            <div data-endpoint="POSTadmin-logout"
                 class="tryItOut-error expandable sl-panel sl-outline-none sl-w-full" hidden>
                <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                     role="button">
                    <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                        <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                 data-icon="caret-down"
                                 class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                            </svg>
                        </div>
                        Request failed with error
                    </div>
                </div>
                <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                    <div class="sl-panel__content sl-p-4">
                        <p class="sl-pb-2"><strong class="error-message"></strong></p>
                        <p class="sl-pb-2">Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</p>
                    </div>
                </div>
            </div>

                <div data-endpoint="POSTadmin-logout"
                     class="tryItOut-response expandable sl-panel sl-outline-none sl-w-full" hidden>
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Received response
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="sl-panel__content sl-p-4">
                            <p class="sl-pb-2 response-status"></p>
                            <pre><code class="sl-pb-2 response-content language-json"
                                       data-empty-response-text="<Empty response>"
                                       style="max-height: 300px;"></code></pre>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
                
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                                    <div class="sl--ml-2">
                                        Example request:
                                        <select class="example-request-lang-toggle sl-text-base"
                                                aria-label="Request Sample Language"
                                                onchange="switchExampleLanguage(event.target.value);">
                                                                                            <option>bash</option>
                                                                                            <option>javascript</option>
                                                                                            <option>php</option>
                                                                                            <option>python</option>
                                                                                    </select>
                                    </div>
                                </div>
                            </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-bash"
                                     style="">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-bash">curl --request POST \
    "http://learnhub.test/admin/logout" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-javascript"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-javascript">const url = new URL(
    "http://learnhub.test/admin/logout"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-php"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://learnhub.test/admin/logout';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-python"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-python">import requests
import json

url = 'http://learnhub.test/admin/logout'
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('POST', url, headers=headers)
response.json()</code></pre>                                        </div>
                                    </div>
                                </div>
                                                    </div>
                    
                            </div>
    </div>
</div>

                <h1 id="general"
        class="sl-text-5xl sl-leading-tight sl-font-prose sl-text-heading"
    >
        General
    </h1>

    

                                <div class="sl-stack sl-stack--vertical sl-stack--8 HttpOperation sl-flex sl-flex-col sl-items-stretch sl-w-full">
    <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
        <div class="sl-relative">
            <div class="sl-stack sl-stack--horizontal sl-stack--5 sl-flex sl-flex-row sl-items-center">
                <h2 class="sl-text-3xl sl-leading-tight sl-font-prose sl-text-heading sl-mt-5 sl-mb-1"
                    id="general-GET-">
                    GET /
                </h2>
            </div>
        </div>

        <div class="sl-relative">
            <div title="http://learnhub.test/"
                     class="sl-stack sl-stack--horizontal sl-stack--3 sl-inline-flex sl-flex-row sl-items-center sl-max-w-full sl-font-mono sl-py-2 sl-pr-4 sl-bg-canvas-50 sl-rounded-lg"
                >
                                            <div class="sl-text-lg sl-font-semibold sl-px-2.5 sl-py-1 sl-text-on-primary sl-rounded-lg"
                             style="background-color: green;"
                        >
                            GET
                        </div>
                                        <div class="sl-flex sl-overflow-x-hidden sl-text-lg sl-select-all">
                        <div dir="rtl"
                             class="sl-overflow-x-hidden sl-truncate sl-text-muted">http://learnhub.test</div>
                        <div class="sl-flex-1 sl-font-semibold">/</div>
                    </div>

                                    </div>
        </div>

        
    </div>
    <div class="sl-flex">
        <div data-testid="two-column-left" class="sl-flex-1 sl-w-0">
            <div class="sl-stack sl-stack--vertical sl-stack--10 sl-flex sl-flex-col sl-items-stretch">
                <div class="sl-stack sl-stack--vertical sl-stack--8 sl-flex sl-flex-col sl-items-stretch">
                                            <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">
                                Headers
                            </h3>
                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Content-Type</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Accept</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    
                    

                    
                    
                                    </div>
            </div>
        </div>

        <div data-testid="two-column-right" class="sl-relative sl-w-2/5 sl-ml-16" style="max-width: 500px;">
            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">

                                    <div class="sl-inverted">
    <div class="sl-overflow-y-hidden sl-rounded-lg">
        <form class="TryItPanel sl-bg-canvas-100 sl-rounded-lg"
              data-method="GET"
              data-path="/"
              data-hasfiles="0"
              data-hasjsonbody="0">
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Headers
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                                                            <label aria-hidden="true"
                                       for="header-GET--Content-Type">Content-Type</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Content-Type" name="Content-Type"
                                               id="header-GET--Content-Type"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                                                            <label aria-hidden="true"
                                       for="header-GET--Accept">Accept</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Accept" name="Accept"
                                               id="header-GET--Accept"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
            
            
            
            <div class="SendButtonHolder sl-mt-4 sl-p-4 sl-pt-0">
                <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-center">
                    <button type="button" data-endpoint="GET-"
                            class="tryItOut-btn sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-bg-primary hover:sl-bg-primary-dark active:sl-bg-primary-darker disabled:sl-bg-canvas-100 sl-text-on-primary disabled:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70"
                    >
                        Send Request 💥
                    </button>
                </div>
            </div>

            <div data-endpoint="GET-"
                 class="tryItOut-error expandable sl-panel sl-outline-none sl-w-full" hidden>
                <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                     role="button">
                    <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                        <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                 data-icon="caret-down"
                                 class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                            </svg>
                        </div>
                        Request failed with error
                    </div>
                </div>
                <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                    <div class="sl-panel__content sl-p-4">
                        <p class="sl-pb-2"><strong class="error-message"></strong></p>
                        <p class="sl-pb-2">Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</p>
                    </div>
                </div>
            </div>

                <div data-endpoint="GET-"
                     class="tryItOut-response expandable sl-panel sl-outline-none sl-w-full" hidden>
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Received response
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="sl-panel__content sl-p-4">
                            <p class="sl-pb-2 response-status"></p>
                            <pre><code class="sl-pb-2 response-content language-json"
                                       data-empty-response-text="<Empty response>"
                                       style="max-height: 300px;"></code></pre>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
                
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                                    <div class="sl--ml-2">
                                        Example request:
                                        <select class="example-request-lang-toggle sl-text-base"
                                                aria-label="Request Sample Language"
                                                onchange="switchExampleLanguage(event.target.value);">
                                                                                            <option>bash</option>
                                                                                            <option>javascript</option>
                                                                                            <option>php</option>
                                                                                            <option>python</option>
                                                                                    </select>
                                    </div>
                                </div>
                            </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-bash"
                                     style="">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-bash">curl --request GET \
    --get "http://learnhub.test/" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-javascript"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-javascript">const url = new URL(
    "http://learnhub.test/"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-php"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://learnhub.test/';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-python"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-python">import requests
import json

url = 'http://learnhub.test//'
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>                                        </div>
                                    </div>
                                </div>
                                                    </div>
                    
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-py-2">
                                    <div class="sl--ml-2">
                                        <div class="sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-text-muted sl-rounded sl-border-transparent sl-border">
                                            <div class="sl-mb-2 sl-inline-block">Example response:</div>
                                            <div class="sl-mb-2 sl-inline-block">
                                                <select
                                                        class="example-response-GET--toggle sl-text-base"
                                                        aria-label="Response sample"
                                                        onchange="switchExampleResponse('GET-', event.target.value);">
                                                                                                            <option value="0">200</option>
                                                                                                    </select></div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        class="sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 hover:sl-bg-canvas-50 active:sl-bg-canvas-100 sl-text-muted hover:sl-text-body focus:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70">
                                    <div class="sl-mx-0">
                                        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="copy"
                                             class="svg-inline--fa fa-copy fa-fw fa-sm sl-icon" role="img"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path fill="currentColor"
                                                  d="M384 96L384 0h-112c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48H464c26.51 0 48-21.49 48-48V128h-95.1C398.4 128 384 113.6 384 96zM416 0v96h96L416 0zM192 352V128h-144c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48h192c26.51 0 48-21.49 48-48L288 416h-32C220.7 416 192 387.3 192 352z"></path>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                                                            <div class="sl-panel__content-wrapper sl-bg-canvas-100 example-response-GET- example-response-GET--0"
                                     style=" "
                                >
                                    <div class="sl-panel__content sl-p-0">                                            <details class="sl-pl-2">
                                                <summary style="cursor: pointer; list-style: none;">
                                                    <small>
                                                        <span class="expansion-chevrons">

    <svg aria-hidden="true" focusable="false" data-prefix="fas"
         data-icon="chevron-right"
         class="svg-inline--fa fa-chevron-right fa-fw sl-icon sl-text-muted"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
        <path fill="currentColor"
              d="M96 480c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L242.8 256L73.38 86.63c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l192 192c12.5 12.5 12.5 32.75 0 45.25l-192 192C112.4 476.9 104.2 480 96 480z"></path>
    </svg>
                                                            </span>
                                                        Headers
                                                    </small>
                                                </summary>
                                                <pre><code class="language-http">                                                            cache-control
                                                            : no-cache, private
                                                                                                                    content-type
                                                            : application/json
                                                         </code></pre>
                                            </details>
                                                                                                                                                                        
                                            <pre><code style="max-height: 300px;"
                                                       class="language-json sl-overflow-x-auto sl-overflow-y-auto">{
    &quot;name&quot;: &quot;LearnHub.mk API&quot;,
    &quot;version&quot;: 1,
    &quot;message&quot;: &quot;Welcome to the LearnHub.mk API!&quot;
}</code></pre>
                                                                            </div>
                                </div>
                                                    </div>
                            </div>
    </div>
</div>

                    <div class="sl-stack sl-stack--vertical sl-stack--8 HttpOperation sl-flex sl-flex-col sl-items-stretch sl-w-full">
    <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
        <div class="sl-relative">
            <div class="sl-stack sl-stack--horizontal sl-stack--5 sl-flex sl-flex-row sl-items-center">
                <h2 class="sl-text-3xl sl-leading-tight sl-font-prose sl-text-heading sl-mt-5 sl-mb-1"
                    id="general-GETsanctum-csrf-cookie">
                    Return an empty response simply to trigger the storage of the CSRF cookie in the browser.
                </h2>
            </div>
        </div>

        <div class="sl-relative">
            <div title="http://learnhub.test/sanctum/csrf-cookie"
                     class="sl-stack sl-stack--horizontal sl-stack--3 sl-inline-flex sl-flex-row sl-items-center sl-max-w-full sl-font-mono sl-py-2 sl-pr-4 sl-bg-canvas-50 sl-rounded-lg"
                >
                                            <div class="sl-text-lg sl-font-semibold sl-px-2.5 sl-py-1 sl-text-on-primary sl-rounded-lg"
                             style="background-color: green;"
                        >
                            GET
                        </div>
                                        <div class="sl-flex sl-overflow-x-hidden sl-text-lg sl-select-all">
                        <div dir="rtl"
                             class="sl-overflow-x-hidden sl-truncate sl-text-muted">http://learnhub.test</div>
                        <div class="sl-flex-1 sl-font-semibold">/sanctum/csrf-cookie</div>
                    </div>

                                    </div>
        </div>

        
    </div>
    <div class="sl-flex">
        <div data-testid="two-column-left" class="sl-flex-1 sl-w-0">
            <div class="sl-stack sl-stack--vertical sl-stack--10 sl-flex sl-flex-col sl-items-stretch">
                <div class="sl-stack sl-stack--vertical sl-stack--8 sl-flex sl-flex-col sl-items-stretch">
                                            <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">
                                Headers
                            </h3>
                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Content-Type</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Accept</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    
                    

                    
                    
                                    </div>
            </div>
        </div>

        <div data-testid="two-column-right" class="sl-relative sl-w-2/5 sl-ml-16" style="max-width: 500px;">
            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">

                                    <div class="sl-inverted">
    <div class="sl-overflow-y-hidden sl-rounded-lg">
        <form class="TryItPanel sl-bg-canvas-100 sl-rounded-lg"
              data-method="GET"
              data-path="sanctum/csrf-cookie"
              data-hasfiles="0"
              data-hasjsonbody="0">
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Headers
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                                                            <label aria-hidden="true"
                                       for="header-GETsanctum-csrf-cookie-Content-Type">Content-Type</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Content-Type" name="Content-Type"
                                               id="header-GETsanctum-csrf-cookie-Content-Type"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                                                            <label aria-hidden="true"
                                       for="header-GETsanctum-csrf-cookie-Accept">Accept</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Accept" name="Accept"
                                               id="header-GETsanctum-csrf-cookie-Accept"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
            
            
            
            <div class="SendButtonHolder sl-mt-4 sl-p-4 sl-pt-0">
                <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-center">
                    <button type="button" data-endpoint="GETsanctum-csrf-cookie"
                            class="tryItOut-btn sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-bg-primary hover:sl-bg-primary-dark active:sl-bg-primary-darker disabled:sl-bg-canvas-100 sl-text-on-primary disabled:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70"
                    >
                        Send Request 💥
                    </button>
                </div>
            </div>

            <div data-endpoint="GETsanctum-csrf-cookie"
                 class="tryItOut-error expandable sl-panel sl-outline-none sl-w-full" hidden>
                <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                     role="button">
                    <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                        <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                 data-icon="caret-down"
                                 class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                            </svg>
                        </div>
                        Request failed with error
                    </div>
                </div>
                <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                    <div class="sl-panel__content sl-p-4">
                        <p class="sl-pb-2"><strong class="error-message"></strong></p>
                        <p class="sl-pb-2">Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</p>
                    </div>
                </div>
            </div>

                <div data-endpoint="GETsanctum-csrf-cookie"
                     class="tryItOut-response expandable sl-panel sl-outline-none sl-w-full" hidden>
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Received response
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="sl-panel__content sl-p-4">
                            <p class="sl-pb-2 response-status"></p>
                            <pre><code class="sl-pb-2 response-content language-json"
                                       data-empty-response-text="<Empty response>"
                                       style="max-height: 300px;"></code></pre>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
                
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                                    <div class="sl--ml-2">
                                        Example request:
                                        <select class="example-request-lang-toggle sl-text-base"
                                                aria-label="Request Sample Language"
                                                onchange="switchExampleLanguage(event.target.value);">
                                                                                            <option>bash</option>
                                                                                            <option>javascript</option>
                                                                                            <option>php</option>
                                                                                            <option>python</option>
                                                                                    </select>
                                    </div>
                                </div>
                            </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-bash"
                                     style="">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-bash">curl --request GET \
    --get "http://learnhub.test/sanctum/csrf-cookie" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-javascript"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-javascript">const url = new URL(
    "http://learnhub.test/sanctum/csrf-cookie"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-php"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://learnhub.test/sanctum/csrf-cookie';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-python"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-python">import requests
import json

url = 'http://learnhub.test/sanctum/csrf-cookie'
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>                                        </div>
                                    </div>
                                </div>
                                                    </div>
                    
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-py-2">
                                    <div class="sl--ml-2">
                                        <div class="sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-text-muted sl-rounded sl-border-transparent sl-border">
                                            <div class="sl-mb-2 sl-inline-block">Example response:</div>
                                            <div class="sl-mb-2 sl-inline-block">
                                                <select
                                                        class="example-response-GETsanctum-csrf-cookie-toggle sl-text-base"
                                                        aria-label="Response sample"
                                                        onchange="switchExampleResponse('GETsanctum-csrf-cookie', event.target.value);">
                                                                                                            <option value="0">204</option>
                                                                                                    </select></div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        class="sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 hover:sl-bg-canvas-50 active:sl-bg-canvas-100 sl-text-muted hover:sl-text-body focus:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70">
                                    <div class="sl-mx-0">
                                        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="copy"
                                             class="svg-inline--fa fa-copy fa-fw fa-sm sl-icon" role="img"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path fill="currentColor"
                                                  d="M384 96L384 0h-112c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48H464c26.51 0 48-21.49 48-48V128h-95.1C398.4 128 384 113.6 384 96zM416 0v96h96L416 0zM192 352V128h-144c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48h192c26.51 0 48-21.49 48-48L288 416h-32C220.7 416 192 387.3 192 352z"></path>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                                                            <div class="sl-panel__content-wrapper sl-bg-canvas-100 example-response-GETsanctum-csrf-cookie example-response-GETsanctum-csrf-cookie-0"
                                     style=" "
                                >
                                    <div class="sl-panel__content sl-p-0">                                            <details class="sl-pl-2">
                                                <summary style="cursor: pointer; list-style: none;">
                                                    <small>
                                                        <span class="expansion-chevrons">

    <svg aria-hidden="true" focusable="false" data-prefix="fas"
         data-icon="chevron-right"
         class="svg-inline--fa fa-chevron-right fa-fw sl-icon sl-text-muted"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
        <path fill="currentColor"
              d="M96 480c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L242.8 256L73.38 86.63c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l192 192c12.5 12.5 12.5 32.75 0 45.25l-192 192C112.4 476.9 104.2 480 96 480z"></path>
    </svg>
                                                            </span>
                                                        Headers
                                                    </small>
                                                </summary>
                                                <pre><code class="language-http">                                                            cache-control
                                                            : no-cache, private
                                                                                                                    access-control-allow-origin
                                                            : *
                                                                                                                    set-cookie
                                                            : XSRF-TOKEN=eyJpdiI6IkJUUTRjam5ZT3lBcEc3Rlp5cTRFOXc9PSIsInZhbHVlIjoiekVjYkN2NmI1bzA2Z2g5aktzQWhJZTlzZHFSQmVaN2dBekJ2MlA2OFlrMS9nQ2xjSm5vVlFMNkhzQjBMcGpPYWxYV2RzSHdJdGx4Yit2OGY1cVM2c3M3c2lydVdnNzN5YmxuSG1OR01VTVRIdCs5MkNUMDArc3ZMdkYwUmxaNEsiLCJtYWMiOiI3YjlhZTUyZGU2ZDdmMmM3ZDU1MGY3MWZiNDAzMjM4N2YwZjg5OWZmOWZhODI0MmMyZjNmZmRmNmY5MzE0ODE0IiwidGFnIjoiIn0%3D; expires=Wed, 17 Apr 2024 05:10:42 GMT; Max-Age=7200; path=/; samesite=lax; learnhub_session=eyJpdiI6Ik5wbVcyNFFNZWFBdUV3MEFFSnVKNXc9PSIsInZhbHVlIjoiTm1tTEs1TjZrN0VqK29SdDVtNnBjaTZBV2dhdWtXZTJoNTVEUFpXV3AyUUUyMTZoRWxHdHNaNmNlSkpBNC9wWWVZK0tQTEpDbG5aMzg4MU5uK2ZkRkl3NjlXRkxaM01rRTBNNjlpRXZPV3N3SERQUk8veU5tbFlURy8yZGlSbEgiLCJtYWMiOiI5YWYwNzkyNjEyYjlmMDA0Zjc2MmE2MjJmMjBjZWQ1ZDI2MjEzMTdkZTgyMDQ2NDNmNTVmMjdhNjRmNjFlYTZhIiwidGFnIjoiIn0%3D; expires=Wed, 17 Apr 2024 05:10:42 GMT; Max-Age=7200; path=/; httponly; samesite=lax
                                                         </code></pre>
                                            </details>
                                                                                                                            <pre><code>[Empty response]</code></pre>
                                                                            </div>
                                </div>
                                                    </div>
                            </div>
    </div>
</div>

                <h1 id="website"
        class="sl-text-5xl sl-leading-tight sl-font-prose sl-text-heading"
    >
        Website
    </h1>

    

                                <div class="sl-stack sl-stack--vertical sl-stack--8 HttpOperation sl-flex sl-flex-col sl-items-stretch sl-w-full">
    <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
        <div class="sl-relative">
            <div class="sl-stack sl-stack--horizontal sl-stack--5 sl-flex sl-flex-row sl-items-center">
                <h2 class="sl-text-3xl sl-leading-tight sl-font-prose sl-text-heading sl-mt-5 sl-mb-1"
                    id="website-GETblog-posts">
                    Blog posts
                </h2>
            </div>
        </div>

        <div class="sl-relative">
            <div title="http://learnhub.test/blog-posts"
                     class="sl-stack sl-stack--horizontal sl-stack--3 sl-inline-flex sl-flex-row sl-items-center sl-max-w-full sl-font-mono sl-py-2 sl-pr-4 sl-bg-canvas-50 sl-rounded-lg"
                >
                                            <div class="sl-text-lg sl-font-semibold sl-px-2.5 sl-py-1 sl-text-on-primary sl-rounded-lg"
                             style="background-color: green;"
                        >
                            GET
                        </div>
                                        <div class="sl-flex sl-overflow-x-hidden sl-text-lg sl-select-all">
                        <div dir="rtl"
                             class="sl-overflow-x-hidden sl-truncate sl-text-muted">http://learnhub.test</div>
                        <div class="sl-flex-1 sl-font-semibold">/blog-posts</div>
                    </div>

                                    </div>
        </div>

        <p>This endpoint list all blog post from newest ones to the oldest.Additionally you may use ?title= query param to filter by title</p>
    </div>
    <div class="sl-flex">
        <div data-testid="two-column-left" class="sl-flex-1 sl-w-0">
            <div class="sl-stack sl-stack--vertical sl-stack--10 sl-flex sl-flex-col sl-items-stretch">
                <div class="sl-stack sl-stack--vertical sl-stack--8 sl-flex sl-flex-col sl-items-stretch">
                                            <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">
                                Headers
                            </h3>
                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Content-Type</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Accept</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    
                    

                                                <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">
                                <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">Query Parameters</h3>

                                <div class="sl-text-sm">
                                                                            <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">title</div>
                                            <span class="sl-truncate sl-text-muted">string</span>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        qui
                    </div>
                </div>
            </div>
            </div>
</div>
                                                                </div>
                        </div>
                    
                    
                                    </div>
            </div>
        </div>

        <div data-testid="two-column-right" class="sl-relative sl-w-2/5 sl-ml-16" style="max-width: 500px;">
            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">

                                    <div class="sl-inverted">
    <div class="sl-overflow-y-hidden sl-rounded-lg">
        <form class="TryItPanel sl-bg-canvas-100 sl-rounded-lg"
              data-method="GET"
              data-path="blog-posts"
              data-hasfiles="0"
              data-hasjsonbody="0">
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Headers
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                                                            <label aria-hidden="true"
                                       for="header-GETblog-posts-Content-Type">Content-Type</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Content-Type" name="Content-Type"
                                               id="header-GETblog-posts-Content-Type"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                                                            <label aria-hidden="true"
                                       for="header-GETblog-posts-Accept">Accept</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Accept" name="Accept"
                                               id="header-GETblog-posts-Accept"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Query Parameters
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                                                            <label aria-hidden="true"
                                       for="queryparam-GETblog-posts-title">title</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                                                                    <input aria-label="title" name="title"
                                                   id="queryparam-GETblog-posts-title"
                                                   placeholder=""
                                                   value="qui" data-component="query"
                                                   class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border"
                                            >
                                                                            </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
            
            <div class="SendButtonHolder sl-mt-4 sl-p-4 sl-pt-0">
                <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-center">
                    <button type="button" data-endpoint="GETblog-posts"
                            class="tryItOut-btn sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-bg-primary hover:sl-bg-primary-dark active:sl-bg-primary-darker disabled:sl-bg-canvas-100 sl-text-on-primary disabled:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70"
                    >
                        Send Request 💥
                    </button>
                </div>
            </div>

            <div data-endpoint="GETblog-posts"
                 class="tryItOut-error expandable sl-panel sl-outline-none sl-w-full" hidden>
                <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                     role="button">
                    <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                        <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                 data-icon="caret-down"
                                 class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                            </svg>
                        </div>
                        Request failed with error
                    </div>
                </div>
                <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                    <div class="sl-panel__content sl-p-4">
                        <p class="sl-pb-2"><strong class="error-message"></strong></p>
                        <p class="sl-pb-2">Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</p>
                    </div>
                </div>
            </div>

                <div data-endpoint="GETblog-posts"
                     class="tryItOut-response expandable sl-panel sl-outline-none sl-w-full" hidden>
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Received response
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="sl-panel__content sl-p-4">
                            <p class="sl-pb-2 response-status"></p>
                            <pre><code class="sl-pb-2 response-content language-json"
                                       data-empty-response-text="<Empty response>"
                                       style="max-height: 300px;"></code></pre>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
                
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                                    <div class="sl--ml-2">
                                        Example request:
                                        <select class="example-request-lang-toggle sl-text-base"
                                                aria-label="Request Sample Language"
                                                onchange="switchExampleLanguage(event.target.value);">
                                                                                            <option>bash</option>
                                                                                            <option>javascript</option>
                                                                                            <option>php</option>
                                                                                            <option>python</option>
                                                                                    </select>
                                    </div>
                                </div>
                            </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-bash"
                                     style="">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-bash">curl --request GET \
    --get "http://learnhub.test/blog-posts?title=qui" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-javascript"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-javascript">const url = new URL(
    "http://learnhub.test/blog-posts"
);

const params = {
    "title": "qui",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-php"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://learnhub.test/blog-posts';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'title' =&gt; 'qui',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-python"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-python">import requests
import json

url = 'http://learnhub.test/blog-posts'
params = {
  'title': 'qui',
}
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>                                        </div>
                                    </div>
                                </div>
                                                    </div>
                    
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-py-2">
                                    <div class="sl--ml-2">
                                        <div class="sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-text-muted sl-rounded sl-border-transparent sl-border">
                                            <div class="sl-mb-2 sl-inline-block">Example response:</div>
                                            <div class="sl-mb-2 sl-inline-block">
                                                <select
                                                        class="example-response-GETblog-posts-toggle sl-text-base"
                                                        aria-label="Response sample"
                                                        onchange="switchExampleResponse('GETblog-posts', event.target.value);">
                                                                                                            <option value="0">200</option>
                                                                                                    </select></div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        class="sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 hover:sl-bg-canvas-50 active:sl-bg-canvas-100 sl-text-muted hover:sl-text-body focus:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70">
                                    <div class="sl-mx-0">
                                        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="copy"
                                             class="svg-inline--fa fa-copy fa-fw fa-sm sl-icon" role="img"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path fill="currentColor"
                                                  d="M384 96L384 0h-112c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48H464c26.51 0 48-21.49 48-48V128h-95.1C398.4 128 384 113.6 384 96zM416 0v96h96L416 0zM192 352V128h-144c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48h192c26.51 0 48-21.49 48-48L288 416h-32C220.7 416 192 387.3 192 352z"></path>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                                                            <div class="sl-panel__content-wrapper sl-bg-canvas-100 example-response-GETblog-posts example-response-GETblog-posts-0"
                                     style=" "
                                >
                                    <div class="sl-panel__content sl-p-0">                                            <details class="sl-pl-2">
                                                <summary style="cursor: pointer; list-style: none;">
                                                    <small>
                                                        <span class="expansion-chevrons">

    <svg aria-hidden="true" focusable="false" data-prefix="fas"
         data-icon="chevron-right"
         class="svg-inline--fa fa-chevron-right fa-fw sl-icon sl-text-muted"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
        <path fill="currentColor"
              d="M96 480c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L242.8 256L73.38 86.63c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l192 192c12.5 12.5 12.5 32.75 0 45.25l-192 192C112.4 476.9 104.2 480 96 480z"></path>
    </svg>
                                                            </span>
                                                        Headers
                                                    </small>
                                                </summary>
                                                <pre><code class="language-http">                                                            cache-control
                                                            : no-cache, private
                                                                                                                    content-type
                                                            : application/json
                                                         </code></pre>
                                            </details>
                                                                                                                                                                        
                                            <pre><code style="max-height: 300px;"
                                                       class="language-json sl-overflow-x-auto sl-overflow-y-auto">{
    &quot;data&quot;: [],
    &quot;links&quot;: {
        &quot;first&quot;: &quot;http://learnhub.test/blog-posts?page=1&quot;,
        &quot;last&quot;: &quot;http://learnhub.test/blog-posts?page=1&quot;,
        &quot;prev&quot;: null,
        &quot;next&quot;: null
    },
    &quot;meta&quot;: {
        &quot;current_page&quot;: 1,
        &quot;from&quot;: null,
        &quot;last_page&quot;: 1,
        &quot;links&quot;: [
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;&amp;laquo; Previous&quot;,
                &quot;active&quot;: false
            },
            {
                &quot;url&quot;: &quot;http://learnhub.test/blog-posts?page=1&quot;,
                &quot;label&quot;: &quot;1&quot;,
                &quot;active&quot;: true
            },
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;Next &amp;raquo;&quot;,
                &quot;active&quot;: false
            }
        ],
        &quot;path&quot;: &quot;http://learnhub.test/blog-posts&quot;,
        &quot;per_page&quot;: 15,
        &quot;to&quot;: null,
        &quot;total&quot;: 0
    }
}</code></pre>
                                                                            </div>
                                </div>
                                                    </div>
                            </div>
    </div>
</div>

                    <div class="sl-stack sl-stack--vertical sl-stack--8 HttpOperation sl-flex sl-flex-col sl-items-stretch sl-w-full">
    <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
        <div class="sl-relative">
            <div class="sl-stack sl-stack--horizontal sl-stack--5 sl-flex sl-flex-row sl-items-center">
                <h2 class="sl-text-3xl sl-leading-tight sl-font-prose sl-text-heading sl-mt-5 sl-mb-1"
                    id="website-GETblog-posts--slug-">
                    Blog Post
                </h2>
            </div>
        </div>

        <div class="sl-relative">
            <div title="http://learnhub.test/blog-posts/{slug}"
                     class="sl-stack sl-stack--horizontal sl-stack--3 sl-inline-flex sl-flex-row sl-items-center sl-max-w-full sl-font-mono sl-py-2 sl-pr-4 sl-bg-canvas-50 sl-rounded-lg"
                >
                                            <div class="sl-text-lg sl-font-semibold sl-px-2.5 sl-py-1 sl-text-on-primary sl-rounded-lg"
                             style="background-color: green;"
                        >
                            GET
                        </div>
                                        <div class="sl-flex sl-overflow-x-hidden sl-text-lg sl-select-all">
                        <div dir="rtl"
                             class="sl-overflow-x-hidden sl-truncate sl-text-muted">http://learnhub.test</div>
                        <div class="sl-flex-1 sl-font-semibold">/blog-posts/{slug}</div>
                    </div>

                                    </div>
        </div>

        <p>This endpoint retrieves blog post by a slug.</p>
    </div>
    <div class="sl-flex">
        <div data-testid="two-column-left" class="sl-flex-1 sl-w-0">
            <div class="sl-stack sl-stack--vertical sl-stack--10 sl-flex sl-flex-col sl-items-stretch">
                <div class="sl-stack sl-stack--vertical sl-stack--8 sl-flex sl-flex-col sl-items-stretch">
                                            <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">
                                Headers
                            </h3>
                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Content-Type</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Accept</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    
                                            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">URL Parameters</h3>

                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">slug</div>
                                            <span class="sl-truncate sl-text-muted">string</span>
                                    </div>
                                    <div class="sl-flex-1 sl-h-px sl-mx-3"></div>
                    <span class="sl-ml-2 sl-text-warning">required</span>
                                    </div>
                <div class="sl-prose sl-markdown-viewer" style="font-size: 12px;">
            <p>The slug of the blog post.</p>
        </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        repellendus
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    

                    
                    
                                    </div>
            </div>
        </div>

        <div data-testid="two-column-right" class="sl-relative sl-w-2/5 sl-ml-16" style="max-width: 500px;">
            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">

                                    <div class="sl-inverted">
    <div class="sl-overflow-y-hidden sl-rounded-lg">
        <form class="TryItPanel sl-bg-canvas-100 sl-rounded-lg"
              data-method="GET"
              data-path="blog-posts/{slug}"
              data-hasfiles="0"
              data-hasjsonbody="0">
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Headers
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                                                            <label aria-hidden="true"
                                       for="header-GETblog-posts--slug--Content-Type">Content-Type</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Content-Type" name="Content-Type"
                                               id="header-GETblog-posts--slug--Content-Type"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                                                            <label aria-hidden="true"
                                       for="header-GETblog-posts--slug--Accept">Accept</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Accept" name="Accept"
                                               id="header-GETblog-posts--slug--Accept"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            URL Parameters
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                            <label aria-hidden="true"
                                       for="urlparam-GETblog-posts--slug--slug">slug</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="slug" name="slug"
                                               id="urlparam-GETblog-posts--slug--slug"
                                               placeholder="The slug of the blog post."
                                               value="repellendus" data-component="url"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
            
            
            <div class="SendButtonHolder sl-mt-4 sl-p-4 sl-pt-0">
                <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-center">
                    <button type="button" data-endpoint="GETblog-posts--slug-"
                            class="tryItOut-btn sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-bg-primary hover:sl-bg-primary-dark active:sl-bg-primary-darker disabled:sl-bg-canvas-100 sl-text-on-primary disabled:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70"
                    >
                        Send Request 💥
                    </button>
                </div>
            </div>

            <div data-endpoint="GETblog-posts--slug-"
                 class="tryItOut-error expandable sl-panel sl-outline-none sl-w-full" hidden>
                <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                     role="button">
                    <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                        <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                 data-icon="caret-down"
                                 class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                            </svg>
                        </div>
                        Request failed with error
                    </div>
                </div>
                <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                    <div class="sl-panel__content sl-p-4">
                        <p class="sl-pb-2"><strong class="error-message"></strong></p>
                        <p class="sl-pb-2">Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</p>
                    </div>
                </div>
            </div>

                <div data-endpoint="GETblog-posts--slug-"
                     class="tryItOut-response expandable sl-panel sl-outline-none sl-w-full" hidden>
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Received response
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="sl-panel__content sl-p-4">
                            <p class="sl-pb-2 response-status"></p>
                            <pre><code class="sl-pb-2 response-content language-json"
                                       data-empty-response-text="<Empty response>"
                                       style="max-height: 300px;"></code></pre>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
                
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                                    <div class="sl--ml-2">
                                        Example request:
                                        <select class="example-request-lang-toggle sl-text-base"
                                                aria-label="Request Sample Language"
                                                onchange="switchExampleLanguage(event.target.value);">
                                                                                            <option>bash</option>
                                                                                            <option>javascript</option>
                                                                                            <option>php</option>
                                                                                            <option>python</option>
                                                                                    </select>
                                    </div>
                                </div>
                            </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-bash"
                                     style="">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-bash">curl --request GET \
    --get "http://learnhub.test/blog-posts/repellendus" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-javascript"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-javascript">const url = new URL(
    "http://learnhub.test/blog-posts/repellendus"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-php"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://learnhub.test/blog-posts/repellendus';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-python"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-python">import requests
import json

url = 'http://learnhub.test/blog-posts/repellendus'
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>                                        </div>
                                    </div>
                                </div>
                                                    </div>
                    
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-py-2">
                                    <div class="sl--ml-2">
                                        <div class="sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-text-muted sl-rounded sl-border-transparent sl-border">
                                            <div class="sl-mb-2 sl-inline-block">Example response:</div>
                                            <div class="sl-mb-2 sl-inline-block">
                                                <select
                                                        class="example-response-GETblog-posts--slug--toggle sl-text-base"
                                                        aria-label="Response sample"
                                                        onchange="switchExampleResponse('GETblog-posts--slug-', event.target.value);">
                                                                                                            <option value="0">404</option>
                                                                                                    </select></div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        class="sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 hover:sl-bg-canvas-50 active:sl-bg-canvas-100 sl-text-muted hover:sl-text-body focus:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70">
                                    <div class="sl-mx-0">
                                        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="copy"
                                             class="svg-inline--fa fa-copy fa-fw fa-sm sl-icon" role="img"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path fill="currentColor"
                                                  d="M384 96L384 0h-112c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48H464c26.51 0 48-21.49 48-48V128h-95.1C398.4 128 384 113.6 384 96zM416 0v96h96L416 0zM192 352V128h-144c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48h192c26.51 0 48-21.49 48-48L288 416h-32C220.7 416 192 387.3 192 352z"></path>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                                                            <div class="sl-panel__content-wrapper sl-bg-canvas-100 example-response-GETblog-posts--slug- example-response-GETblog-posts--slug--0"
                                     style=" "
                                >
                                    <div class="sl-panel__content sl-p-0">                                            <details class="sl-pl-2">
                                                <summary style="cursor: pointer; list-style: none;">
                                                    <small>
                                                        <span class="expansion-chevrons">

    <svg aria-hidden="true" focusable="false" data-prefix="fas"
         data-icon="chevron-right"
         class="svg-inline--fa fa-chevron-right fa-fw sl-icon sl-text-muted"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
        <path fill="currentColor"
              d="M96 480c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L242.8 256L73.38 86.63c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l192 192c12.5 12.5 12.5 32.75 0 45.25l-192 192C112.4 476.9 104.2 480 96 480z"></path>
    </svg>
                                                            </span>
                                                        Headers
                                                    </small>
                                                </summary>
                                                <pre><code class="language-http">                                                            cache-control
                                                            : no-cache, private
                                                                                                                    content-type
                                                            : application/json
                                                         </code></pre>
                                            </details>
                                                                                                                                                                        
                                            <pre><code style="max-height: 300px;"
                                                       class="language-json sl-overflow-x-auto sl-overflow-y-auto">{
    &quot;message&quot;: &quot;No query results for model [App\\Website\\Models\\BlogPost].&quot;,
    &quot;exception&quot;: &quot;Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException&quot;,
    &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/Handler.php&quot;,
    &quot;line&quot;: 628,
    &quot;trace&quot;: [
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/Handler.php&quot;,
            &quot;line&quot;: 576,
            &quot;function&quot;: &quot;prepareException&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Exceptions\\Handler&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/nunomaduro/collision/src/Adapters/Laravel/ExceptionHandler.php&quot;,
            &quot;line&quot;: 54,
            &quot;function&quot;: &quot;render&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Exceptions\\Handler&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php&quot;,
            &quot;line&quot;: 51,
            &quot;function&quot;: &quot;render&quot;,
            &quot;class&quot;: &quot;NunoMaduro\\Collision\\Adapters\\Laravel\\ExceptionHandler&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php&quot;,
            &quot;line&quot;: 146,
            &quot;function&quot;: &quot;handleException&quot;,
            &quot;class&quot;: &quot;Illuminate\\Routing\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php&quot;,
            &quot;line&quot;: 119,
            &quot;function&quot;: &quot;Illuminate\\Pipeline\\{closure}&quot;,
            &quot;class&quot;: &quot;Illuminate\\Pipeline\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Router.php&quot;,
            &quot;line&quot;: 805,
            &quot;function&quot;: &quot;then&quot;,
            &quot;class&quot;: &quot;Illuminate\\Pipeline\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Router.php&quot;,
            &quot;line&quot;: 784,
            &quot;function&quot;: &quot;runRouteWithinStack&quot;,
            &quot;class&quot;: &quot;Illuminate\\Routing\\Router&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Router.php&quot;,
            &quot;line&quot;: 748,
            &quot;function&quot;: &quot;runRoute&quot;,
            &quot;class&quot;: &quot;Illuminate\\Routing\\Router&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Router.php&quot;,
            &quot;line&quot;: 737,
            &quot;function&quot;: &quot;dispatchToRoute&quot;,
            &quot;class&quot;: &quot;Illuminate\\Routing\\Router&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php&quot;,
            &quot;line&quot;: 200,
            &quot;function&quot;: &quot;dispatch&quot;,
            &quot;class&quot;: &quot;Illuminate\\Routing\\Router&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php&quot;,
            &quot;line&quot;: 144,
            &quot;function&quot;: &quot;Illuminate\\Foundation\\Http\\{closure}&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Http\\Kernel&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php&quot;,
            &quot;line&quot;: 21,
            &quot;function&quot;: &quot;Illuminate\\Pipeline\\{closure}&quot;,
            &quot;class&quot;: &quot;Illuminate\\Pipeline\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php&quot;,
            &quot;line&quot;: 31,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php&quot;,
            &quot;line&quot;: 183,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php&quot;,
            &quot;line&quot;: 21,
            &quot;function&quot;: &quot;Illuminate\\Pipeline\\{closure}&quot;,
            &quot;class&quot;: &quot;Illuminate\\Pipeline\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php&quot;,
            &quot;line&quot;: 51,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php&quot;,
            &quot;line&quot;: 183,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Http\\Middleware\\TrimStrings&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php&quot;,
            &quot;line&quot;: 27,
            &quot;function&quot;: &quot;Illuminate\\Pipeline\\{closure}&quot;,
            &quot;class&quot;: &quot;Illuminate\\Pipeline\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php&quot;,
            &quot;line&quot;: 183,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Illuminate\\Http\\Middleware\\ValidatePostSize&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php&quot;,
            &quot;line&quot;: 110,
            &quot;function&quot;: &quot;Illuminate\\Pipeline\\{closure}&quot;,
            &quot;class&quot;: &quot;Illuminate\\Pipeline\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php&quot;,
            &quot;line&quot;: 183,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php&quot;,
            &quot;line&quot;: 49,
            &quot;function&quot;: &quot;Illuminate\\Pipeline\\{closure}&quot;,
            &quot;class&quot;: &quot;Illuminate\\Pipeline\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php&quot;,
            &quot;line&quot;: 183,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Illuminate\\Http\\Middleware\\HandleCors&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php&quot;,
            &quot;line&quot;: 57,
            &quot;function&quot;: &quot;Illuminate\\Pipeline\\{closure}&quot;,
            &quot;class&quot;: &quot;Illuminate\\Pipeline\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php&quot;,
            &quot;line&quot;: 183,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Illuminate\\Http\\Middleware\\TrustProxies&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php&quot;,
            &quot;line&quot;: 119,
            &quot;function&quot;: &quot;Illuminate\\Pipeline\\{closure}&quot;,
            &quot;class&quot;: &quot;Illuminate\\Pipeline\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php&quot;,
            &quot;line&quot;: 175,
            &quot;function&quot;: &quot;then&quot;,
            &quot;class&quot;: &quot;Illuminate\\Pipeline\\Pipeline&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php&quot;,
            &quot;line&quot;: 144,
            &quot;function&quot;: &quot;sendRequestThroughRouter&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Http\\Kernel&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/Extracting/Strategies/Responses/ResponseCalls.php&quot;,
            &quot;line&quot;: 300,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Http\\Kernel&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/Extracting/Strategies/Responses/ResponseCalls.php&quot;,
            &quot;line&quot;: 288,
            &quot;function&quot;: &quot;callLaravelOrLumenRoute&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\Extracting\\Strategies\\Responses\\ResponseCalls&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/Extracting/Strategies/Responses/ResponseCalls.php&quot;,
            &quot;line&quot;: 91,
            &quot;function&quot;: &quot;makeApiCall&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\Extracting\\Strategies\\Responses\\ResponseCalls&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/Extracting/Strategies/Responses/ResponseCalls.php&quot;,
            &quot;line&quot;: 44,
            &quot;function&quot;: &quot;makeResponseCall&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\Extracting\\Strategies\\Responses\\ResponseCalls&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/Extracting/Strategies/Responses/ResponseCalls.php&quot;,
            &quot;line&quot;: 35,
            &quot;function&quot;: &quot;makeResponseCallIfConditionsPass&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\Extracting\\Strategies\\Responses\\ResponseCalls&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/Extracting/Extractor.php&quot;,
            &quot;line&quot;: 236,
            &quot;function&quot;: &quot;__invoke&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\Extracting\\Strategies\\Responses\\ResponseCalls&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/Extracting/Extractor.php&quot;,
            &quot;line&quot;: 163,
            &quot;function&quot;: &quot;iterateThroughStrategies&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\Extracting\\Extractor&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/Extracting/Extractor.php&quot;,
            &quot;line&quot;: 95,
            &quot;function&quot;: &quot;fetchResponses&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\Extracting\\Extractor&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/GroupedEndpoints/GroupedEndpointsFromApp.php&quot;,
            &quot;line&quot;: 125,
            &quot;function&quot;: &quot;processRoute&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\Extracting\\Extractor&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/GroupedEndpoints/GroupedEndpointsFromApp.php&quot;,
            &quot;line&quot;: 72,
            &quot;function&quot;: &quot;extractEndpointsInfoFromLaravelApp&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\GroupedEndpoints\\GroupedEndpointsFromApp&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/GroupedEndpoints/GroupedEndpointsFromApp.php&quot;,
            &quot;line&quot;: 50,
            &quot;function&quot;: &quot;extractEndpointsInfoAndWriteToDisk&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\GroupedEndpoints\\GroupedEndpointsFromApp&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/knuckleswtf/scribe/src/Commands/GenerateDocumentation.php&quot;,
            &quot;line&quot;: 53,
            &quot;function&quot;: &quot;get&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\GroupedEndpoints\\GroupedEndpointsFromApp&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php&quot;,
            &quot;line&quot;: 36,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Knuckles\\Scribe\\Commands\\GenerateDocumentation&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Container/Util.php&quot;,
            &quot;line&quot;: 41,
            &quot;function&quot;: &quot;Illuminate\\Container\\{closure}&quot;,
            &quot;class&quot;: &quot;Illuminate\\Container\\BoundMethod&quot;,
            &quot;type&quot;: &quot;::&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php&quot;,
            &quot;line&quot;: 93,
            &quot;function&quot;: &quot;unwrapIfClosure&quot;,
            &quot;class&quot;: &quot;Illuminate\\Container\\Util&quot;,
            &quot;type&quot;: &quot;::&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php&quot;,
            &quot;line&quot;: 35,
            &quot;function&quot;: &quot;callBoundMethod&quot;,
            &quot;class&quot;: &quot;Illuminate\\Container\\BoundMethod&quot;,
            &quot;type&quot;: &quot;::&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Container/Container.php&quot;,
            &quot;line&quot;: 662,
            &quot;function&quot;: &quot;call&quot;,
            &quot;class&quot;: &quot;Illuminate\\Container\\BoundMethod&quot;,
            &quot;type&quot;: &quot;::&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Console/Command.php&quot;,
            &quot;line&quot;: 212,
            &quot;function&quot;: &quot;call&quot;,
            &quot;class&quot;: &quot;Illuminate\\Container\\Container&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/symfony/console/Command/Command.php&quot;,
            &quot;line&quot;: 279,
            &quot;function&quot;: &quot;execute&quot;,
            &quot;class&quot;: &quot;Illuminate\\Console\\Command&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Console/Command.php&quot;,
            &quot;line&quot;: 181,
            &quot;function&quot;: &quot;run&quot;,
            &quot;class&quot;: &quot;Symfony\\Component\\Console\\Command\\Command&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/symfony/console/Application.php&quot;,
            &quot;line&quot;: 1049,
            &quot;function&quot;: &quot;run&quot;,
            &quot;class&quot;: &quot;Illuminate\\Console\\Command&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/symfony/console/Application.php&quot;,
            &quot;line&quot;: 318,
            &quot;function&quot;: &quot;doRunCommand&quot;,
            &quot;class&quot;: &quot;Symfony\\Component\\Console\\Application&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/symfony/console/Application.php&quot;,
            &quot;line&quot;: 169,
            &quot;function&quot;: &quot;doRun&quot;,
            &quot;class&quot;: &quot;Symfony\\Component\\Console\\Application&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php&quot;,
            &quot;line&quot;: 196,
            &quot;function&quot;: &quot;run&quot;,
            &quot;class&quot;: &quot;Symfony\\Component\\Console\\Application&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        },
        {
            &quot;file&quot;: &quot;/var/www/html/artisan&quot;,
            &quot;line&quot;: 35,
            &quot;function&quot;: &quot;handle&quot;,
            &quot;class&quot;: &quot;Illuminate\\Foundation\\Console\\Kernel&quot;,
            &quot;type&quot;: &quot;-&gt;&quot;
        }
    ]
}</code></pre>
                                                                            </div>
                                </div>
                                                    </div>
                            </div>
    </div>
</div>

                    <div class="sl-stack sl-stack--vertical sl-stack--8 HttpOperation sl-flex sl-flex-col sl-items-stretch sl-w-full">
    <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
        <div class="sl-relative">
            <div class="sl-stack sl-stack--horizontal sl-stack--5 sl-flex sl-flex-row sl-items-center">
                <h2 class="sl-text-3xl sl-leading-tight sl-font-prose sl-text-heading sl-mt-5 sl-mb-1"
                    id="website-GETblog-post-tags">
                    Blog Post Tags
                </h2>
            </div>
        </div>

        <div class="sl-relative">
            <div title="http://learnhub.test/blog-post-tags"
                     class="sl-stack sl-stack--horizontal sl-stack--3 sl-inline-flex sl-flex-row sl-items-center sl-max-w-full sl-font-mono sl-py-2 sl-pr-4 sl-bg-canvas-50 sl-rounded-lg"
                >
                                            <div class="sl-text-lg sl-font-semibold sl-px-2.5 sl-py-1 sl-text-on-primary sl-rounded-lg"
                             style="background-color: green;"
                        >
                            GET
                        </div>
                                        <div class="sl-flex sl-overflow-x-hidden sl-text-lg sl-select-all">
                        <div dir="rtl"
                             class="sl-overflow-x-hidden sl-truncate sl-text-muted">http://learnhub.test</div>
                        <div class="sl-flex-1 sl-font-semibold">/blog-post-tags</div>
                    </div>

                                    </div>
        </div>

        <p>This endpoint list all blog post tags in alphabetically order paginated by 20.</p>
    </div>
    <div class="sl-flex">
        <div data-testid="two-column-left" class="sl-flex-1 sl-w-0">
            <div class="sl-stack sl-stack--vertical sl-stack--10 sl-flex sl-flex-col sl-items-stretch">
                <div class="sl-stack sl-stack--vertical sl-stack--8 sl-flex sl-flex-col sl-items-stretch">
                                            <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">
                                Headers
                            </h3>
                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Content-Type</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Accept</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    
                    

                    
                    
                                    </div>
            </div>
        </div>

        <div data-testid="two-column-right" class="sl-relative sl-w-2/5 sl-ml-16" style="max-width: 500px;">
            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">

                                    <div class="sl-inverted">
    <div class="sl-overflow-y-hidden sl-rounded-lg">
        <form class="TryItPanel sl-bg-canvas-100 sl-rounded-lg"
              data-method="GET"
              data-path="blog-post-tags"
              data-hasfiles="0"
              data-hasjsonbody="0">
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Headers
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                                                            <label aria-hidden="true"
                                       for="header-GETblog-post-tags-Content-Type">Content-Type</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Content-Type" name="Content-Type"
                                               id="header-GETblog-post-tags-Content-Type"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                                                            <label aria-hidden="true"
                                       for="header-GETblog-post-tags-Accept">Accept</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Accept" name="Accept"
                                               id="header-GETblog-post-tags-Accept"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
            
            
            
            <div class="SendButtonHolder sl-mt-4 sl-p-4 sl-pt-0">
                <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-center">
                    <button type="button" data-endpoint="GETblog-post-tags"
                            class="tryItOut-btn sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-bg-primary hover:sl-bg-primary-dark active:sl-bg-primary-darker disabled:sl-bg-canvas-100 sl-text-on-primary disabled:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70"
                    >
                        Send Request 💥
                    </button>
                </div>
            </div>

            <div data-endpoint="GETblog-post-tags"
                 class="tryItOut-error expandable sl-panel sl-outline-none sl-w-full" hidden>
                <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                     role="button">
                    <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                        <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                 data-icon="caret-down"
                                 class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                            </svg>
                        </div>
                        Request failed with error
                    </div>
                </div>
                <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                    <div class="sl-panel__content sl-p-4">
                        <p class="sl-pb-2"><strong class="error-message"></strong></p>
                        <p class="sl-pb-2">Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</p>
                    </div>
                </div>
            </div>

                <div data-endpoint="GETblog-post-tags"
                     class="tryItOut-response expandable sl-panel sl-outline-none sl-w-full" hidden>
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Received response
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="sl-panel__content sl-p-4">
                            <p class="sl-pb-2 response-status"></p>
                            <pre><code class="sl-pb-2 response-content language-json"
                                       data-empty-response-text="<Empty response>"
                                       style="max-height: 300px;"></code></pre>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
                
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                                    <div class="sl--ml-2">
                                        Example request:
                                        <select class="example-request-lang-toggle sl-text-base"
                                                aria-label="Request Sample Language"
                                                onchange="switchExampleLanguage(event.target.value);">
                                                                                            <option>bash</option>
                                                                                            <option>javascript</option>
                                                                                            <option>php</option>
                                                                                            <option>python</option>
                                                                                    </select>
                                    </div>
                                </div>
                            </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-bash"
                                     style="">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-bash">curl --request GET \
    --get "http://learnhub.test/blog-post-tags" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-javascript"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-javascript">const url = new URL(
    "http://learnhub.test/blog-post-tags"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-php"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://learnhub.test/blog-post-tags';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-python"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-python">import requests
import json

url = 'http://learnhub.test/blog-post-tags'
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>                                        </div>
                                    </div>
                                </div>
                                                    </div>
                    
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-py-2">
                                    <div class="sl--ml-2">
                                        <div class="sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-text-muted sl-rounded sl-border-transparent sl-border">
                                            <div class="sl-mb-2 sl-inline-block">Example response:</div>
                                            <div class="sl-mb-2 sl-inline-block">
                                                <select
                                                        class="example-response-GETblog-post-tags-toggle sl-text-base"
                                                        aria-label="Response sample"
                                                        onchange="switchExampleResponse('GETblog-post-tags', event.target.value);">
                                                                                                            <option value="0">200</option>
                                                                                                    </select></div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        class="sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 hover:sl-bg-canvas-50 active:sl-bg-canvas-100 sl-text-muted hover:sl-text-body focus:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70">
                                    <div class="sl-mx-0">
                                        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="copy"
                                             class="svg-inline--fa fa-copy fa-fw fa-sm sl-icon" role="img"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path fill="currentColor"
                                                  d="M384 96L384 0h-112c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48H464c26.51 0 48-21.49 48-48V128h-95.1C398.4 128 384 113.6 384 96zM416 0v96h96L416 0zM192 352V128h-144c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48h192c26.51 0 48-21.49 48-48L288 416h-32C220.7 416 192 387.3 192 352z"></path>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                                                            <div class="sl-panel__content-wrapper sl-bg-canvas-100 example-response-GETblog-post-tags example-response-GETblog-post-tags-0"
                                     style=" "
                                >
                                    <div class="sl-panel__content sl-p-0">                                            <details class="sl-pl-2">
                                                <summary style="cursor: pointer; list-style: none;">
                                                    <small>
                                                        <span class="expansion-chevrons">

    <svg aria-hidden="true" focusable="false" data-prefix="fas"
         data-icon="chevron-right"
         class="svg-inline--fa fa-chevron-right fa-fw sl-icon sl-text-muted"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
        <path fill="currentColor"
              d="M96 480c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L242.8 256L73.38 86.63c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l192 192c12.5 12.5 12.5 32.75 0 45.25l-192 192C112.4 476.9 104.2 480 96 480z"></path>
    </svg>
                                                            </span>
                                                        Headers
                                                    </small>
                                                </summary>
                                                <pre><code class="language-http">                                                            cache-control
                                                            : no-cache, private
                                                                                                                    content-type
                                                            : application/json
                                                         </code></pre>
                                            </details>
                                                                                                                                                                        
                                            <pre><code style="max-height: 300px;"
                                                       class="language-json sl-overflow-x-auto sl-overflow-y-auto">{
    &quot;data&quot;: [],
    &quot;links&quot;: {
        &quot;first&quot;: &quot;http://learnhub.test/blog-post-tags?page=1&quot;,
        &quot;last&quot;: &quot;http://learnhub.test/blog-post-tags?page=1&quot;,
        &quot;prev&quot;: null,
        &quot;next&quot;: null
    },
    &quot;meta&quot;: {
        &quot;current_page&quot;: 1,
        &quot;from&quot;: null,
        &quot;last_page&quot;: 1,
        &quot;links&quot;: [
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;&amp;laquo; Previous&quot;,
                &quot;active&quot;: false
            },
            {
                &quot;url&quot;: &quot;http://learnhub.test/blog-post-tags?page=1&quot;,
                &quot;label&quot;: &quot;1&quot;,
                &quot;active&quot;: true
            },
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;Next &amp;raquo;&quot;,
                &quot;active&quot;: false
            }
        ],
        &quot;path&quot;: &quot;http://learnhub.test/blog-post-tags&quot;,
        &quot;per_page&quot;: 20,
        &quot;to&quot;: null,
        &quot;total&quot;: 0
    }
}</code></pre>
                                                                            </div>
                                </div>
                                                    </div>
                            </div>
    </div>
</div>

                    <div class="sl-stack sl-stack--vertical sl-stack--8 HttpOperation sl-flex sl-flex-col sl-items-stretch sl-w-full">
    <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
        <div class="sl-relative">
            <div class="sl-stack sl-stack--horizontal sl-stack--5 sl-flex sl-flex-row sl-items-center">
                <h2 class="sl-text-3xl sl-leading-tight sl-font-prose sl-text-heading sl-mt-5 sl-mb-1"
                    id="website-GETblog-post-tags--tag-">
                    Blog Post By Tag
                </h2>
            </div>
        </div>

        <div class="sl-relative">
            <div title="http://learnhub.test/blog-post-tags/{tag}"
                     class="sl-stack sl-stack--horizontal sl-stack--3 sl-inline-flex sl-flex-row sl-items-center sl-max-w-full sl-font-mono sl-py-2 sl-pr-4 sl-bg-canvas-50 sl-rounded-lg"
                >
                                            <div class="sl-text-lg sl-font-semibold sl-px-2.5 sl-py-1 sl-text-on-primary sl-rounded-lg"
                             style="background-color: green;"
                        >
                            GET
                        </div>
                                        <div class="sl-flex sl-overflow-x-hidden sl-text-lg sl-select-all">
                        <div dir="rtl"
                             class="sl-overflow-x-hidden sl-truncate sl-text-muted">http://learnhub.test</div>
                        <div class="sl-flex-1 sl-font-semibold">/blog-post-tags/{tag}</div>
                    </div>

                                    </div>
        </div>

        <p>This endpoint retrieves blog posts by a specific tag.</p>
    </div>
    <div class="sl-flex">
        <div data-testid="two-column-left" class="sl-flex-1 sl-w-0">
            <div class="sl-stack sl-stack--vertical sl-stack--10 sl-flex sl-flex-col sl-items-stretch">
                <div class="sl-stack sl-stack--vertical sl-stack--8 sl-flex sl-flex-col sl-items-stretch">
                                            <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">
                                Headers
                            </h3>
                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Content-Type</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Accept</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    
                                            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">URL Parameters</h3>

                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">tag</div>
                                            <span class="sl-truncate sl-text-muted">string</span>
                                    </div>
                                    <div class="sl-flex-1 sl-h-px sl-mx-3"></div>
                    <span class="sl-ml-2 sl-text-warning">required</span>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        reiciendis
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    

                    
                    
                                    </div>
            </div>
        </div>

        <div data-testid="two-column-right" class="sl-relative sl-w-2/5 sl-ml-16" style="max-width: 500px;">
            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">

                                    <div class="sl-inverted">
    <div class="sl-overflow-y-hidden sl-rounded-lg">
        <form class="TryItPanel sl-bg-canvas-100 sl-rounded-lg"
              data-method="GET"
              data-path="blog-post-tags/{tag}"
              data-hasfiles="0"
              data-hasjsonbody="0">
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Headers
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                                                            <label aria-hidden="true"
                                       for="header-GETblog-post-tags--tag--Content-Type">Content-Type</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Content-Type" name="Content-Type"
                                               id="header-GETblog-post-tags--tag--Content-Type"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                                                            <label aria-hidden="true"
                                       for="header-GETblog-post-tags--tag--Accept">Accept</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Accept" name="Accept"
                                               id="header-GETblog-post-tags--tag--Accept"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            URL Parameters
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                            <label aria-hidden="true"
                                       for="urlparam-GETblog-post-tags--tag--tag">tag</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="tag" name="tag"
                                               id="urlparam-GETblog-post-tags--tag--tag"
                                               placeholder=""
                                               value="reiciendis" data-component="url"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
            
            
            <div class="SendButtonHolder sl-mt-4 sl-p-4 sl-pt-0">
                <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-center">
                    <button type="button" data-endpoint="GETblog-post-tags--tag-"
                            class="tryItOut-btn sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-bg-primary hover:sl-bg-primary-dark active:sl-bg-primary-darker disabled:sl-bg-canvas-100 sl-text-on-primary disabled:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70"
                    >
                        Send Request 💥
                    </button>
                </div>
            </div>

            <div data-endpoint="GETblog-post-tags--tag-"
                 class="tryItOut-error expandable sl-panel sl-outline-none sl-w-full" hidden>
                <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                     role="button">
                    <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                        <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                 data-icon="caret-down"
                                 class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                            </svg>
                        </div>
                        Request failed with error
                    </div>
                </div>
                <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                    <div class="sl-panel__content sl-p-4">
                        <p class="sl-pb-2"><strong class="error-message"></strong></p>
                        <p class="sl-pb-2">Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</p>
                    </div>
                </div>
            </div>

                <div data-endpoint="GETblog-post-tags--tag-"
                     class="tryItOut-response expandable sl-panel sl-outline-none sl-w-full" hidden>
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Received response
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="sl-panel__content sl-p-4">
                            <p class="sl-pb-2 response-status"></p>
                            <pre><code class="sl-pb-2 response-content language-json"
                                       data-empty-response-text="<Empty response>"
                                       style="max-height: 300px;"></code></pre>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
                
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                                    <div class="sl--ml-2">
                                        Example request:
                                        <select class="example-request-lang-toggle sl-text-base"
                                                aria-label="Request Sample Language"
                                                onchange="switchExampleLanguage(event.target.value);">
                                                                                            <option>bash</option>
                                                                                            <option>javascript</option>
                                                                                            <option>php</option>
                                                                                            <option>python</option>
                                                                                    </select>
                                    </div>
                                </div>
                            </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-bash"
                                     style="">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-bash">curl --request GET \
    --get "http://learnhub.test/blog-post-tags/reiciendis" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-javascript"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-javascript">const url = new URL(
    "http://learnhub.test/blog-post-tags/reiciendis"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-php"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://learnhub.test/blog-post-tags/reiciendis';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-python"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-python">import requests
import json

url = 'http://learnhub.test/blog-post-tags/reiciendis'
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>                                        </div>
                                    </div>
                                </div>
                                                    </div>
                    
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-py-2">
                                    <div class="sl--ml-2">
                                        <div class="sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-text-muted sl-rounded sl-border-transparent sl-border">
                                            <div class="sl-mb-2 sl-inline-block">Example response:</div>
                                            <div class="sl-mb-2 sl-inline-block">
                                                <select
                                                        class="example-response-GETblog-post-tags--tag--toggle sl-text-base"
                                                        aria-label="Response sample"
                                                        onchange="switchExampleResponse('GETblog-post-tags--tag-', event.target.value);">
                                                                                                            <option value="0">200</option>
                                                                                                    </select></div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        class="sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 hover:sl-bg-canvas-50 active:sl-bg-canvas-100 sl-text-muted hover:sl-text-body focus:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70">
                                    <div class="sl-mx-0">
                                        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="copy"
                                             class="svg-inline--fa fa-copy fa-fw fa-sm sl-icon" role="img"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path fill="currentColor"
                                                  d="M384 96L384 0h-112c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48H464c26.51 0 48-21.49 48-48V128h-95.1C398.4 128 384 113.6 384 96zM416 0v96h96L416 0zM192 352V128h-144c-26.51 0-48 21.49-48 48v288c0 26.51 21.49 48 48 48h192c26.51 0 48-21.49 48-48L288 416h-32C220.7 416 192 387.3 192 352z"></path>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                                                            <div class="sl-panel__content-wrapper sl-bg-canvas-100 example-response-GETblog-post-tags--tag- example-response-GETblog-post-tags--tag--0"
                                     style=" "
                                >
                                    <div class="sl-panel__content sl-p-0">                                            <details class="sl-pl-2">
                                                <summary style="cursor: pointer; list-style: none;">
                                                    <small>
                                                        <span class="expansion-chevrons">

    <svg aria-hidden="true" focusable="false" data-prefix="fas"
         data-icon="chevron-right"
         class="svg-inline--fa fa-chevron-right fa-fw sl-icon sl-text-muted"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
        <path fill="currentColor"
              d="M96 480c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L242.8 256L73.38 86.63c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l192 192c12.5 12.5 12.5 32.75 0 45.25l-192 192C112.4 476.9 104.2 480 96 480z"></path>
    </svg>
                                                            </span>
                                                        Headers
                                                    </small>
                                                </summary>
                                                <pre><code class="language-http">                                                            cache-control
                                                            : no-cache, private
                                                                                                                    content-type
                                                            : application/json
                                                         </code></pre>
                                            </details>
                                                                                                                                                                        
                                            <pre><code style="max-height: 300px;"
                                                       class="language-json sl-overflow-x-auto sl-overflow-y-auto">{
    &quot;data&quot;: [],
    &quot;links&quot;: {
        &quot;first&quot;: &quot;http://learnhub.test/blog-post-tags/reiciendis?page=1&quot;,
        &quot;last&quot;: &quot;http://learnhub.test/blog-post-tags/reiciendis?page=1&quot;,
        &quot;prev&quot;: null,
        &quot;next&quot;: null
    },
    &quot;meta&quot;: {
        &quot;current_page&quot;: 1,
        &quot;from&quot;: null,
        &quot;last_page&quot;: 1,
        &quot;links&quot;: [
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;&amp;laquo; Previous&quot;,
                &quot;active&quot;: false
            },
            {
                &quot;url&quot;: &quot;http://learnhub.test/blog-post-tags/reiciendis?page=1&quot;,
                &quot;label&quot;: &quot;1&quot;,
                &quot;active&quot;: true
            },
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;Next &amp;raquo;&quot;,
                &quot;active&quot;: false
            }
        ],
        &quot;path&quot;: &quot;http://learnhub.test/blog-post-tags/reiciendis&quot;,
        &quot;per_page&quot;: 15,
        &quot;to&quot;: null,
        &quot;total&quot;: 0
    }
}</code></pre>
                                                                            </div>
                                </div>
                                                    </div>
                            </div>
    </div>
</div>

                    <div class="sl-stack sl-stack--vertical sl-stack--8 HttpOperation sl-flex sl-flex-col sl-items-stretch sl-w-full">
    <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
        <div class="sl-relative">
            <div class="sl-stack sl-stack--horizontal sl-stack--5 sl-flex sl-flex-row sl-items-center">
                <h2 class="sl-text-3xl sl-leading-tight sl-font-prose sl-text-heading sl-mt-5 sl-mb-1"
                    id="website-POSTcontact">
                    Contact
                </h2>
            </div>
        </div>

        <div class="sl-relative">
            <div title="http://learnhub.test/contact"
                     class="sl-stack sl-stack--horizontal sl-stack--3 sl-inline-flex sl-flex-row sl-items-center sl-max-w-full sl-font-mono sl-py-2 sl-pr-4 sl-bg-canvas-50 sl-rounded-lg"
                >
                                            <div class="sl-text-lg sl-font-semibold sl-px-2.5 sl-py-1 sl-text-on-primary sl-rounded-lg"
                             style="background-color: black;"
                        >
                            POST
                        </div>
                                        <div class="sl-flex sl-overflow-x-hidden sl-text-lg sl-select-all">
                        <div dir="rtl"
                             class="sl-overflow-x-hidden sl-truncate sl-text-muted">http://learnhub.test</div>
                        <div class="sl-flex-1 sl-font-semibold">/contact</div>
                    </div>

                                    </div>
        </div>

        <p>This endpoint is used for submitting contact form data.</p>
    </div>
    <div class="sl-flex">
        <div data-testid="two-column-left" class="sl-flex-1 sl-w-0">
            <div class="sl-stack sl-stack--vertical sl-stack--10 sl-flex sl-flex-col sl-items-stretch">
                <div class="sl-stack sl-stack--vertical sl-stack--8 sl-flex sl-flex-col sl-items-stretch">
                                            <div class="sl-stack sl-stack--vertical sl-stack--5 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">
                                Headers
                            </h3>
                            <div class="sl-text-sm">
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Content-Type</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                                    <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">Accept</div>
                                    </div>
                                    </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        application/json
                    </div>
                </div>
            </div>
            </div>
</div>
                                                            </div>
                        </div>
                    
                    

                    
                                            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">
                            <h3 class="sl-text-2xl sl-leading-snug sl-font-prose">Body Parameters</h3>

                                <div class="sl-text-sm">
                                    <div class="expandable sl-text-sm sl-border-l sl-ml-px">
        <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">first_name</div>
                                            <span class="sl-truncate sl-text-muted">string</span>
                                    </div>
                                    <div class="sl-flex-1 sl-h-px sl-mx-3"></div>
                    <span class="sl-ml-2 sl-text-warning">required</span>
                                    </div>
                <div class="sl-prose sl-markdown-viewer" style="font-size: 12px;">
            <p>The first name of the user.</p>
        </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        quo
                    </div>
                </div>
            </div>
            </div>
</div>

            </div>
    <div class="expandable sl-text-sm sl-border-l sl-ml-px">
        <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">last_name</div>
                                            <span class="sl-truncate sl-text-muted">string</span>
                                    </div>
                                    <div class="sl-flex-1 sl-h-px sl-mx-3"></div>
                    <span class="sl-ml-2 sl-text-warning">required</span>
                                    </div>
                <div class="sl-prose sl-markdown-viewer" style="font-size: 12px;">
            <p>The last name of the user.</p>
        </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        fuga
                    </div>
                </div>
            </div>
            </div>
</div>

            </div>
    <div class="expandable sl-text-sm sl-border-l sl-ml-px">
        <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">email</div>
                                            <span class="sl-truncate sl-text-muted">string</span>
                                    </div>
                                    <div class="sl-flex-1 sl-h-px sl-mx-3"></div>
                    <span class="sl-ml-2 sl-text-warning">required</span>
                                    </div>
                <div class="sl-prose sl-markdown-viewer" style="font-size: 12px;">
            <p>The email address of the user.</p>
        </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        jeffrey.mitchell@example.org
                    </div>
                </div>
            </div>
            </div>
</div>

            </div>
    <div class="expandable sl-text-sm sl-border-l sl-ml-px">
        <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">subject</div>
                                            <span class="sl-truncate sl-text-muted">string</span>
                                    </div>
                                    <div class="sl-flex-1 sl-h-px sl-mx-3"></div>
                    <span class="sl-ml-2 sl-text-warning">required</span>
                                    </div>
                <div class="sl-prose sl-markdown-viewer" style="font-size: 12px;">
            <p>The subject of the message.</p>
        </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        provident
                    </div>
                </div>
            </div>
            </div>
</div>

            </div>
    <div class="expandable sl-text-sm sl-border-l sl-ml-px">
        <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">message</div>
                                            <span class="sl-truncate sl-text-muted">string</span>
                                    </div>
                                    <div class="sl-flex-1 sl-h-px sl-mx-3"></div>
                    <span class="sl-ml-2 sl-text-warning">required</span>
                                    </div>
                <div class="sl-prose sl-markdown-viewer" style="font-size: 12px;">
            <p>The body of the message.</p>
        </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        natus
                    </div>
                </div>
            </div>
            </div>
</div>

            </div>
    <div class="expandable sl-text-sm sl-border-l sl-ml-px">
        <div class="sl-flex sl-relative sl-max-w-full sl-py-2 sl-pl-3">
    <div class="sl-w-1 sl-mt-2 sl-mr-3 sl--ml-3 sl-border-t"></div>
    <div class="sl-stack sl-stack--vertical sl-stack--1 sl-flex sl-flex-1 sl-flex-col sl-items-stretch sl-max-w-full sl-ml-2 ">
        <div class="sl-flex sl-items-center sl-max-w-full">
                                        <div class="sl-flex sl-items-baseline sl-text-base">
                    <div class="sl-font-mono sl-font-semibold sl-mr-2">cf-turnstile-response</div>
                                            <span class="sl-truncate sl-text-muted">string</span>
                                    </div>
                                    <div class="sl-flex-1 sl-h-px sl-mx-3"></div>
                    <span class="sl-ml-2 sl-text-warning">required</span>
                                    </div>
                <div class="sl-prose sl-markdown-viewer" style="font-size: 12px;">
            <p>Cloudflare Turnstile ReCaptcha token</p>
        </div>
                                            <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-baseline sl-text-muted">
                <span>Example:</span> <!-- <span> important for spacing -->
                <div class="sl-flex sl-flex-1 sl-flex-wrap" style="gap: 4px;">
                    <div class="sl-max-w-full sl-break-all sl-px-1 sl-bg-canvas-tint sl-text-muted sl-rounded sl-border">
                        modi
                    </div>
                </div>
            </div>
            </div>
</div>

            </div>
                            </div>
                        </div>
                    
                                    </div>
            </div>
        </div>

        <div data-testid="two-column-right" class="sl-relative sl-w-2/5 sl-ml-16" style="max-width: 500px;">
            <div class="sl-stack sl-stack--vertical sl-stack--6 sl-flex sl-flex-col sl-items-stretch">

                                    <div class="sl-inverted">
    <div class="sl-overflow-y-hidden sl-rounded-lg">
        <form class="TryItPanel sl-bg-canvas-100 sl-rounded-lg"
              data-method="POST"
              data-path="contact"
              data-hasfiles="0"
              data-hasjsonbody="1">
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Headers
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="ParameterGrid sl-p-4">
                                                                                            <label aria-hidden="true"
                                       for="header-POSTcontact-Content-Type">Content-Type</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Content-Type" name="Content-Type"
                                               id="header-POSTcontact-Content-Type"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                                                            <label aria-hidden="true"
                                       for="header-POSTcontact-Accept">Accept</label>
                                <span class="sl-mx-3">:</span>
                                <div class="sl-flex sl-flex-1">
                                    <div class="sl-input sl-flex-1 sl-relative">
                                        <input aria-label="Accept" name="Accept"
                                               id="header-POSTcontact-Accept"
                                               value="application/json" data-component="header"
                                               class="sl-relative sl-w-full sl-h-md sl-text-base sl-pr-2.5 sl-pl-2.5 sl-rounded sl-border-transparent hover:sl-border-input focus:sl-border-primary sl-border">
                                    </div>
                                </div>
                                                    </div>
                    </div>
                </div>
            
            
            
                            <div class="sl-panel sl-outline-none sl-w-full expandable">
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Body
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                                                    <div class="TextRequestBody sl-p-4">
                                <div class="code-editor language-json"
                                     id="json-body-POSTcontact"
                                     style="font-family: var(--font-code); font-size: 12px; line-height: var(--lh-code);"
                                >{
    "first_name": "quo",
    "last_name": "fuga",
    "email": "jeffrey.mitchell@example.org",
    "subject": "provident",
    "message": "natus",
    "cf-turnstile-response": "modi"
}</div>
                            </div>
                                            </div>
                </div>
            
            <div class="SendButtonHolder sl-mt-4 sl-p-4 sl-pt-0">
                <div class="sl-stack sl-stack--horizontal sl-stack--2 sl-flex sl-flex-row sl-items-center">
                    <button type="button" data-endpoint="POSTcontact"
                            class="tryItOut-btn sl-button sl-h-sm sl-text-base sl-font-medium sl-px-1.5 sl-bg-primary hover:sl-bg-primary-dark active:sl-bg-primary-darker disabled:sl-bg-canvas-100 sl-text-on-primary disabled:sl-text-body sl-rounded sl-border-transparent sl-border disabled:sl-opacity-70"
                    >
                        Send Request 💥
                    </button>
                </div>
            </div>

            <div data-endpoint="POSTcontact"
                 class="tryItOut-error expandable sl-panel sl-outline-none sl-w-full" hidden>
                <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                     role="button">
                    <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                        <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                 data-icon="caret-down"
                                 class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                <path fill="currentColor"
                                      d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                            </svg>
                        </div>
                        Request failed with error
                    </div>
                </div>
                <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                    <div class="sl-panel__content sl-p-4">
                        <p class="sl-pb-2"><strong class="error-message"></strong></p>
                        <p class="sl-pb-2">Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</p>
                    </div>
                </div>
            </div>

                <div data-endpoint="POSTcontact"
                     class="tryItOut-response expandable sl-panel sl-outline-none sl-w-full" hidden>
                    <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-4 sl-pl-3 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-cursor-pointer sl-select-none"
                         role="button">
                        <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                            <div class="sl-flex sl-items-center sl-mr-1.5 expansion-chevrons expansion-chevrons-solid expanded">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="caret-down"
                                     class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path fill="currentColor"
                                          d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
                                </svg>
                            </div>
                            Received response
                        </div>
                    </div>
                    <div class="sl-panel__content-wrapper sl-bg-canvas-100 children" role="region">
                        <div class="sl-panel__content sl-p-4">
                            <p class="sl-pb-2 response-status"></p>
                            <pre><code class="sl-pb-2 response-content language-json"
                                       data-empty-response-text="<Empty response>"
                                       style="max-height: 300px;"></code></pre>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
                
                                            <div class="sl-panel sl-outline-none sl-w-full sl-rounded-lg">
                            <div class="sl-panel__titlebar sl-flex sl-items-center sl-relative focus:sl-z-10 sl-text-base sl-leading-none sl-pr-3 sl-pl-4 sl-bg-canvas-200 sl-text-body sl-border-input focus:sl-border-primary sl-select-none">
                                <div class="sl-flex sl-flex-1 sl-items-center sl-h-lg">
                                    <div class="sl--ml-2">
                                        Example request:
                                        <select class="example-request-lang-toggle sl-text-base"
                                                aria-label="Request Sample Language"
                                                onchange="switchExampleLanguage(event.target.value);">
                                                                                            <option>bash</option>
                                                                                            <option>javascript</option>
                                                                                            <option>php</option>
                                                                                            <option>python</option>
                                                                                    </select>
                                    </div>
                                </div>
                            </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-bash"
                                     style="">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-bash">curl --request POST \
    "http://learnhub.test/contact" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"first_name\": \"quo\",
    \"last_name\": \"fuga\",
    \"email\": \"jeffrey.mitchell@example.org\",
    \"subject\": \"provident\",
    \"message\": \"natus\",
    \"cf-turnstile-response\": \"modi\"
}"
</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-javascript"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-javascript">const url = new URL(
    "http://learnhub.test/contact"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "first_name": "quo",
    "last_name": "fuga",
    "email": "jeffrey.mitchell@example.org",
    "subject": "provident",
    "message": "natus",
    "cf-turnstile-response": "modi"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-php"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://learnhub.test/contact';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'first_name' =&gt; 'quo',
            'last_name' =&gt; 'fuga',
            'email' =&gt; 'jeffrey.mitchell@example.org',
            'subject' =&gt; 'provident',
            'message' =&gt; 'natus',
            'cf-turnstile-response' =&gt; 'modi',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>                                        </div>
                                    </div>
                                </div>
                                                            <div class="sl-bg-canvas-100 example-request example-request-python"
                                     style="display: none;">
                                    <div class="sl-px-0 sl-py-1">
                                        <div style="max-height: 400px;" class="sl-overflow-y-auto sl-rounded">
                                            <pre><code class="language-python">import requests
import json

url = 'http://learnhub.test/contact'
payload = {
    "first_name": "quo",
    "last_name": "fuga",
    "email": "jeffrey.mitchell@example.org",
    "subject": "provident",
    "message": "natus",
    "cf-turnstile-response": "modi"
}
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre>                                        </div>
                                    </div>
                                </div>
                                                    </div>
                    
                            </div>
    </div>
</div>

            

        <div class="sl-prose sl-markdown-viewer sl-my-5">
            
        </div>
    </div>

</div>

<template id="expand-chevron">
    <svg aria-hidden="true" focusable="false" data-prefix="fas"
         data-icon="chevron-right"
         class="svg-inline--fa fa-chevron-right fa-fw sl-icon sl-text-muted"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
        <path fill="currentColor"
              d="M96 480c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L242.8 256L73.38 86.63c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l192 192c12.5 12.5 12.5 32.75 0 45.25l-192 192C112.4 476.9 104.2 480 96 480z"></path>
    </svg>
</template>

<template id="expanded-chevron">
    <svg aria-hidden="true" focusable="false" data-prefix="fas"
         data-icon="chevron-down"
         class="svg-inline--fa fa-chevron-down fa-fw sl-icon sl-text-muted"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
        <path fill="currentColor"
              d="M224 416c-8.188 0-16.38-3.125-22.62-9.375l-192-192c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L224 338.8l169.4-169.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-192 192C240.4 412.9 232.2 416 224 416z"></path>
    </svg>
</template>

<template id="expand-chevron-solid">
    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="caret-right"
         class="svg-inline--fa fa-caret-right fa-fw sl-icon" role="img" xmlns="http://www.w3.org/2000/svg"
         viewBox="0 0 256 512">
        <path fill="currentColor"
              d="M118.6 105.4l128 127.1C252.9 239.6 256 247.8 256 255.1s-3.125 16.38-9.375 22.63l-128 127.1c-9.156 9.156-22.91 11.9-34.88 6.943S64 396.9 64 383.1V128c0-12.94 7.781-24.62 19.75-29.58S109.5 96.23 118.6 105.4z"></path>
    </svg>
</template>

<template id="expanded-chevron-solid">
    <svg aria-hidden="true" focusable="false" data-prefix="fas"
         data-icon="caret-down"
         class="svg-inline--fa fa-caret-down fa-fw sl-icon" role="img"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
        <path fill="currentColor"
              d="M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z"></path>
    </svg>
</template>
</body>
</html>
