<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Добро пожаловать</title>

        <link rel="stylesheet" href="{{asset('assets/styles/helpers.css')}}">

        <style>

            input:-webkit-autofill,
            input:-webkit-autofill:hover,
            input:-webkit-autofill:focus,
            textarea:-webkit-autofill,
            textarea:-webkit-autofill:hover,
            textarea:-webkit-autofill:focus,
            select:-webkit-autofill,
            select:-webkit-autofill:hover,
            select:-webkit-autofill:focus {
                border: 1px solid #ced4da;
                -webkit-text-fill-color: black;
                -webkit-box-shadow: white;
                transition: background-color 5000s ease-in-out 0s;
            }

            body {
                margin: 0;
            }

            label.in-input {
                position: relative;
                top: 9px;
                left: 9px;
                background-color: white;
                padding: 0 10px;
            }

            input[type="text"],
            input[type="password"] {
                width: calc(100% - 30px - 2px);
                border: 1px solid #ced4da;
                padding: 10px 15px;
                border-radius: 5px;
            }

            button {
                width: 100%;
                cursor: pointer;
                background-color: #009ee3;
                border: unset;
                color: white;
                padding: 10px 15px;
                border-radius: 5px;
            }
            button:focus,
            button:hover,
            button:active {
                background-color: #0483b9;
            }
        </style>

        <style>
            /* модальное окно */
            .modal-window-component-container {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 10;
            }
            .modal-window-component-container .modal-window-component {
                width: 100vw;
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .modal-window-component-container .modal-window-component .modal-window-shadow {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0 0 0 0.7);
                backdrop-filter: blur(12px);
            }
            .modal-window-component-container .modal-window-component .modal-window-content-container {
                z-index: 1;
                position: relative;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.4);
                transition: transform 500ms;
                background-color: #FFFFFF;
            }
            @media only screen and (max-width: 540px) {
                .modal-window-component-container .modal-window-component .modal-window-content-container {
                    width: 100%;
                    height: 100%;
                    border: unset;
                    border-radius: unset;
                }
            }
            .modal-window-component-container .modal-window-component .modal-window-content-container .modal-window-close-button {
                position: absolute;
                top: 0;
                right: 0;
                cursor: pointer;
                display: flex;
                padding: 8px;
            }
            @media only screen and (max-width: 540px) {
                .modal-window-component-container .modal-window-component .modal-window-content-container .modal-window-close-button {
                    padding-left: 10px;
                    padding-bottom: 10px;
                }
            }
            .modal-window-component-container .modal-window-component .modal-window-content-container .modal-window-close-button path {
                /*fill: black;*/
            }
            .modal-window-component-container .modal-window-component .modal-window-content-container .modal-window-content {
                overflow-y: auto;
                overflow-x: hidden;
                /*max-height: 80vh;*/
                max-height: calc(100vh - 25px - 125px);
                margin: 25px 25px 25px 25px;
                min-width: 150px;
                max-width: 80vw;
                padding-top: 20px;
                padding-bottom: 50px;
            }
            @media only screen and (max-width: 540px) {
                .modal-window-component-container .modal-window-component .modal-window-content-container .modal-window-content {
                    height: 100%;
                    padding-bottom: 75px;
                    max-width: unset;
                }
            }
        </style>

        <script>

            Element.prototype.hide = function () {
                this.classList.add('hide');
            }

            Element.prototype.show = function () {
                this.classList.remove('hide');
            }

            Array.prototype.isEqual = function (array) {
                return (this.length === array.length) && this.every(function(element, index) {
                    if (Array.isArray(element) && Array.isArray(array[index])) {
                        return element.isEqual(array[index])
                    } else if ((Array.isArray(element) && !Array.isArray(array[index]))
                        || (!Array.isArray(element) && Array.isArray(array[index]))) {
                        return false;
                    } else {
                        return element.toString() === array[index].toString();
                    }
                });
            }

            function CreateElement(tag, params, parent) {
                const element = document.createElement(tag);
                if (params.attr) {
                    Object.keys(params.attr).forEach((a) => {
                        element.setAttribute(a, params.attr[a]);
                    });
                }
                if (params.class) {
                    element.className = params.class;
                }
                if (params.events) {
                    Object.keys(params.events).forEach((e) => {
                        element.addEventListener(e, params.events[e]);
                    });
                }
                if (params.content) {
                    element.innerHTML = params.content;
                }
                if (parent) {
                    parent.appendChild(element);
                }
                if (params.childs) {
                    params.childs.forEach((child) => {
                        element.appendChild(child);
                    })
                }
                return element;
            }

            function ModalWindow(content, closingCallback, flash) {
                let documentBody = document.body;
                !flash ? documentBody.classList.add('scroll-off') : '';
                let closeButtonSVG = '<svg width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                    '<circle cx="22.9995" cy="23" r="16" transform="rotate(-45 22.9995 23)" fill="black"/>' +
                    '<path fill-rule="evenodd" clip-rule="evenodd" d="M16.8283 16.8288C16.2603 17.3969 16.2603 18.3178 16.8283 18.8859L20.9425 23L16.8284 27.114C16.2604 27.6821 16.2604 28.603 16.8284 29.1711C17.3965 29.7391 18.3174 29.7391 18.8855 29.1711L22.9995 25.0571L27.1135 29.171C27.6815 29.7391 28.6025 29.7391 29.1705 29.171C29.7386 28.603 29.7386 27.682 29.1705 27.114L25.0565 23L29.1707 18.8859C29.7387 18.3179 29.7387 17.3969 29.1707 16.8289C28.6026 16.2608 27.6817 16.2608 27.1136 16.8289L22.9995 20.943L18.8853 16.8288C18.3173 16.2608 17.3963 16.2608 16.8283 16.8288Z" fill="white"/>' +
                    '</svg>';

                let modalWindowComponentContainer = CreateElement('div', {
                    attr: {
                        class: 'modal-window-component-container',
                    }
                });

                let modalWindowComponent = CreateElement('div', {attr: {class: 'modal-window-component'}}, modalWindowComponentContainer);

                CreateElement('div', {
                    attr: {class: 'modal-window-shadow'}, events: {
                        click: () => {
                            closingCallback ? closingCallback() : '';
                            modalWindowComponentContainer.remove();
                            ScrollOff(flash);
                        }
                    }
                }, modalWindowComponent);

                let modalWindowContainer = CreateElement('div', {
                    attr: {
                        class: 'modal-window-content-container',
                    }
                }, modalWindowComponent);

                CreateElement('div', {
                    attr: {
                        class: 'modal-window-close-button',
                    },
                    content: closeButtonSVG,
                    events: {
                        click: () => {
                            closingCallback ? closingCallback() : '';
                            modalWindowComponentContainer.remove();
                            ScrollOff(flash);
                        }
                    }
                }, modalWindowContainer);

                let modalWindowContent = CreateElement('div', {
                    attr: {
                        class: 'modal-window-content',
                    }
                }, modalWindowContainer);

                if (typeof content === 'string') {
                    content = CreateElement('div', {
                        content: content
                    });
                }

                modalWindowContent.append(content)

                document.body.append(modalWindowComponentContainer);

                // CloseByScroll(modalWindowComponentContainer, modalWindowContainer, modalWindowContent, () => {
                //     closingCallback ? closingCallback() : '';
                //     modalWindowComponentContainer.slowRemove();
                //     ScrollOff(flash);
                // });

                return modalWindowComponentContainer;

                function ScrollOff(flash) {
                    if (document.querySelectorAll('.modal-window-component-container').length === 1) {
                        setTimeout(() => {
                            !flash ? documentBody.classList.remove('scroll-off') : '';
                        }, 200);
                    }
                }
            }

            function CloseModal(modal) {
                modal.remove();
                if (document.querySelectorAll('.modal-window-component-container').length === 0) {
                    document.body.classList.remove('scroll-off');
                }
            }

        </script>

        @yield('style')

    </head>
    <body>

        <main>

            @yield('content')

        </main>

        @yield('js')

    </body>
</html>
