@extends('app')

@section('style')
    <style>
        [class*=ymaps-2][class*=-ground-pane] {
            /*filter: url(data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg'><filter id='grayscale'><feColorMatrix type='matrix' values='0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0'/></filter></svg>#grayscale);*/
            -webkit-filter: grayscale(100%);
        }

        [class*=-copyrights-pane] {
            display: none;
        }

        .field-container {
            margin-bottom: 10px;
            border-bottom: 1px solid grey;
        }

        input.layout-container {
            display: none;
        }

        input.layout-container + span {
            /*padding: 10px 15px;*/
            margin-bottom: 5px;
            /*border: 1px solid black;*/
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            display: block;
            cursor: pointer;
            overflow: hidden;
        }

        input.layout-container + span > div > div {
            padding: 10px 15px;
        }

        input.layout-container + span div.delete-button {
            background-color: red;
            width: 10%;
        }

        input.layout-container + span:hover {
            background-color: rgba(0, 0, 0, 0.2);
        }

        input[type="radio"]:checked.layout-container + span {
            background-color: rgba(0, 0, 0, 0.3);
        }
    </style>
@stop

@section('content')
    <div
        style="position: fixed; top: 0; z-index: 1; background-color: white; padding: 20px; width: 15vw; height: 100vh; box-shadow: 0 0 10px rgba(0,0,0,0.5);">

        <div id="pointInfoBar" class="hide">

            <div class="field-container">

                <div id="pointName">-</div>

            </div>

            <div class="field-container">

                <div>Широта</div>
                <div id="latitude">-</div>

            </div>

            <div class="field-container">

                <div>Долгота</div>
                <div id="longitude">-</div>

            </div>

            <div>

                <button id="deletePoint">Удалить</button>

            </div>
        </div>

        <div id="lineInfoBar" class="hide">

            <div class="field-container">

                <div>Начальный узел</div>
                <div id="firstPointName">-</div>

            </div>

            <div class="field-container">

                <div>Конечный узел</div>
                <div id="secondPointName">-</div>

            </div>

            <div class="field-container">

                <div>Длинна</div>
                <div id="lineLength">-</div>

            </div>

            <div>

                <button id="deleteLine">Удалить</button>

            </div>

        </div>

    </div>

    <div id="map" style="height: 99vh; width: 100vw"></div>

    <div class="right-bar"
         style="position: fixed; top: 0; right: 0; background-color: white; padding: 20px; width: 15vw; height: 100vh; box-shadow: 0 0 10px rgba(0,0,0,0.5);">

        <a href="{{route('logout')}}">
            <button>Выход</button>
        </a>

        <div>
            <label>Разрешить редактирование</label>
            <input id="isEditableField" type="checkbox" checked>
        </div>

        <div>
            <button onclick="newLayoutModalWindow()" class="mb-5">Новый слой</button>
        </div>

        <div id="layoutsContainer">

        </div>

    </div>
@stop

@section('js')
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=7a99de42-ac61-446d-9b37-2f210ea2231f"
            type="text/javascript"></script>

    <script>

        const getRandomColor = () => {
            //return currentLayout.lineColor
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        const layouts = {}

        let currentLayout;

        const isEditable = () => {
            return isEditableField.checked
        }

        ymaps.ready(init);

        let myMap;

        let allPolyLines = [];
        let allPoints = {};

        let lastCoordinates = [];

        let currentPoint;
        let currentLine;

        let initFinished = false;

        const rightBarSize = document.body.querySelector('.right-bar').getBoundingClientRect().width

        async function init() {

            // создание элемента управления (зум) с определенными параметрами расположения
            const zoomControl = new ymaps.control.ZoomControl({
                options: {
                    position: {
                        right: rightBarSize + 15,
                        top: 10
                    },
                    size: 'small'
                }
            })

            // Создание экземпляра карты и его привязка к контейнеру с
            // заданным id ("map")
            myMap = new ymaps.Map('map', {
                // При инициализации карты, обязательно нужно указать
                // ее центр и коэффициент масштабирования
                center: [55.72, 37.64],
                zoom: 10,
                controls: [zoomControl/*, 'typeSelector'*//*, 'rulerControl'*/]
            }, {
                yandexMapDisablePoiInteractivity: true
            });

            myMap.events.add([
                'click',
            ], (eventClickOnMap) => {

                ShowPointBar()

                const clickCoordinates = eventClickOnMap.get('coords');

                if (!isEditable() || !currentLayout) {
                    return
                }

                requestNewPoint(clickCoordinates)

            });

            await requestOnServer("{{route('layout.all')}}", {}, (result) => {
                let firstSelected = false;
                let firstLayoutId;
                for (const layout of result) {

                    createLayout(layout, !firstSelected)

                    if (!firstSelected) {
                        firstSelected = true;
                        firstLayoutId = layouts[layout.id];
                    }
                }

                if (!initFinished) {
                    requestOnServer("{{route('lines.all')}}", {}, (result) => {
                        result.forEach((line) => {
                            newPolyline(allPoints[line.startPointId], allPoints[line.endPointId], line.id)
                        })

                        initFinished = true;
                    })
                }

                currentLayout = firstLayoutId;
            })
        }

        async function createLayout(layout, isChecked) {
            const layoutContainer = CreateElement('div', {
                class: 'layout-container'
            }, layoutsContainer);

            const layoutLabel = CreateElement('label', {}, layoutContainer);
            const layoutInput = CreateElement('input', {
                attr: {
                    type: 'radio',
                    name: 'layout',
                    class: 'layout-container'
                }
            }, layoutLabel);

            const layoutSpan = CreateElement('span', {}, layoutLabel);

            const layoutSpanContainer = CreateElement('div', {
                class: 'flex'
            }, layoutSpan);

            const layoutTitleContainer = CreateElement('div', {
                content: layout.title,
                attr: {
                    style: 'width: 90%;'
                }
            }, layoutSpanContainer);

            const layoutColor = CreateElement('div', {
                class: 'layout-color',
            }, layoutSpanContainer);

            const layoutDeleteButton = CreateElement('div', {
                content: 'X',
                class: 'delete-button',
            }, layoutSpanContainer);

            layoutDeleteButton.addEventListener('click', (event) => {
                requestOnServer("{{route('layout.delete')}}", {
                    layoutId: layout.id
                }, () => {
                    layoutContainer.remove()

                    currentLayout = undefined;
                    lastCoordinates = []
                    currentPoint = undefined
                    currentLine = undefined

                    document.dispatchEvent(new CustomEvent('delete-layout', {
                        detail: {
                            layoutId: layout.id
                        }
                    }))
                })
            })

            layoutInput.layoutId = layout.id

            layoutInput.addEventListener('change', () => {
                currentLayout = layouts[layout.id]
                lastCoordinates = []
                currentPoint = undefined
                currentLine = undefined
            })

            layoutInput.checked = isChecked;

            const color = layout.data.color
            layoutColor.style.backgroundColor = color;
            layouts[layout.id] = {
                pointColor: color,
                lineColor: color,
                layoutId: layout.id
            }

            currentLayout = layouts[layout.id]
            lastCoordinates = []
            currentPoint = undefined
            currentLine = undefined

            layout.points && layout.points.forEach((point) => {
                newPoint([point.latitude, point.longitude], point.id)
            })

            lastCoordinates = []
            currentPoint = undefined
            currentLine = undefined
        }

        async function requestNewPoint(coordinates) {

            coordinates = getFixedCoordinates(coordinates);

            await requestOnServer("{{route('point.add')}}", {
                latitude: coordinates[0],
                longitude: coordinates[1],
                layoutId: document.body.querySelector('#layoutsContainer input:checked.layout-container').layoutId
            }, (result) => {
                let pointId = result.id
                newPoint(coordinates, pointId)
            })
        }

        function newPoint(coordinates, pointId) {
            const newPoint = new ymaps.Placemark(coordinates, {
                pointId: pointId,
                layoutId: currentLayout.layoutId
            }, {
                preset: 'islands#circleDotIcon',
                iconColor: currentLayout.pointColor,
                draggable: true,
            });

            isEditableField.addEventListener('change', () => {
                newPoint.options.set('draggable', isEditable())
            })

            document.addEventListener('delete-layout', (event) => {
                if (event.detail.layoutId === newPoint.properties.get('layoutId')) {
                    myMap.geoObjects.remove(newPoint);
                }
            })

            let preCurrentPoint = currentPoint
            currentPoint = newPoint
            UpdateLatitudeLongitudeByPoint()

            myMap.geoObjects.add(newPoint)

            allPoints[pointId] = newPoint

            // слушаем перемещение точки
            let oldCoordinates;
            newPoint.events.add([
                'dragstart'
            ], () => {
                oldCoordinates = getFixedCoordinates(newPoint.geometry.getCoordinates())
            })

            newPoint.events.add([
                'dragend'
            ], () => {

                const newCoordinates = getFixedCoordinates(newPoint.geometry.getCoordinates())

                requestOnServer("{{route('point.update')}}", {
                    pointId: newPoint.properties.get('pointId'),
                    latitude: newCoordinates[0],
                    longitude: newCoordinates[1],
                }, () => {

                    ShowPointBar()

                    preCurrentPoint = currentPoint

                    currentPoint = newPoint
                    UpdateLatitudeLongitudeByPoint()

                    lastCoordinates = newCoordinates;

                    allPolyLines.map((everyPolyline) => {
                        const coordinatesPolyline = getFixedCoordinates(everyPolyline.geometry.getCoordinates())
                        const firstCoordinates = coordinatesPolyline[0]
                        const secondCoordinates = coordinatesPolyline[1]

                        if (firstCoordinates.isEqual(oldCoordinates)) {
                            everyPolyline.geometry.setCoordinates([newCoordinates, secondCoordinates])
                        } else if (secondCoordinates.isEqual(oldCoordinates)) {
                            everyPolyline.geometry.setCoordinates([firstCoordinates, newCoordinates])
                        }
                    })
                })
            })

            // слушаем нажатие на точку
            newPoint.events.add([
                'click',
            ], () => {

                ShowPointBar()

                preCurrentPoint = currentPoint
                currentPoint = newPoint
                UpdateLatitudeLongitudeByPoint()
                const clickPointCoordinates = getFixedCoordinates(newPoint.geometry.getCoordinates())

                if (!isEditable()) {
                    return
                }

                let existFlag = false;

                // бежим по всем линиям, и проверям чтобы не было такой же
                allPolyLines.map((everyPolyline) => {
                    const coordinates = getFixedCoordinates(everyPolyline.geometry.getCoordinates())
                    const firstCoordinates = coordinates[0]
                    const secondCoordinates = coordinates[1]

                    // проверяем существует ли уже такая линия (с такими координатами)
                    if (
                        (lastCoordinates.isEqual(firstCoordinates) && clickPointCoordinates.isEqual(secondCoordinates))
                        || (clickPointCoordinates.isEqual(firstCoordinates) && lastCoordinates.isEqual(secondCoordinates))
                    ) {
                        existFlag = true
                    }
                })

                // проверка на сущестование такой линии и лини не должна начиться и заканчиваться в одной точке
                if (!existFlag && !lastCoordinates.isEqual(clickPointCoordinates) && initFinished) {
                    newPolyline(preCurrentPoint, currentPoint)
                }

                lastCoordinates = clickPointCoordinates
            });

            // слушаем нажатие правой кнопки мыши
            newPoint.events.add([
                'contextmenu',
            ], () => {
                currentPoint = newPoint
                UpdateLatitudeLongitudeByPoint()
                lastCoordinates = getFixedCoordinates(newPoint.geometry.getCoordinates())
            });

            // если это не первая точка на карте (есть начальные координаты для построения линии)
            if (lastCoordinates.length !== 0 && initFinished) {
                newPolyline(preCurrentPoint, currentPoint)
            }

            lastCoordinates = coordinates
        }

        function newPolyline(startPoint, endPoint, lineId = 0) {

            if (!startPoint || !endPoint) {
                return;
            }

            const startPointCoordinates = getFixedCoordinates(startPoint.geometry.getCoordinates())
            const endPointCoordinates = getFixedCoordinates(endPoint.geometry.getCoordinates())

            // инициализация прошла, следующие добавленные точки это точки пользователя
            if (lineId === 0) {
                requestOnServer("{{route('lines.add')}}", {
                    startPointId: startPoint.properties.get('pointId'),
                    endPointId: endPoint.properties.get('pointId')
                }, (result) => {
                    polyline.properties.set('lineId', result.id)
                })
            }

            const polyline = new ymaps.Polyline([startPointCoordinates, endPointCoordinates], {
                // hintContent: name,
                // testProp: name,
                lineId: lineId,
                points: {startPoint, endPoint},
                layoutId: startPoint.properties.get('layoutId')
            }, {
                strokeColor: layouts[startPoint.properties.get('layoutId')].lineColor,
                strokeWidth: 5, // ширина линии
                visible: true,

                // Добавляем в контекстное меню новый пункт, позволяющий удалить ломаную.
                // editorMenuManager: function (items) {
                //     items.push({
                //         title: "Удалить линию",
                //         onClick: function () {
                //             myMap.geoObjects.remove(polyline);
                //         }
                //     });
                //     items.push({
                //         title: "Закрыть редактирование",
                //         onClick: function (e) {
                //             polyline.editor.stopEditing();
                //             polyline.editor.stopDrawing();
                //
                //             const coordinates = polyline.geometry.getCoordinates();
                //
                //             saveNewLine(coordinates, name)
                //
                //             console.log(coordinates);
                //         }
                //     });
                //     return items;
                // }
            });

            myMap.geoObjects.add(polyline);

            polyline.properties.set('hintContent', polyline.geometry.getDistance().toFixed(2) + ' м')

            document.addEventListener('delete-layout', (event) => {
                if (event.detail.layoutId === polyline.properties.get('layoutId')) {
                    myMap.geoObjects.remove(polyline);
                }
            })

            document.addEventListener('delete-point', (event) => {
                if (event.detail.pointId === polyline.properties.get('points').startPoint.properties.get('pointId')
                || event.detail.pointId === polyline.properties.get('points').endPoint.properties.get('pointId')) {
                    myMap.geoObjects.remove(polyline);
                }
            })

            // если создали только что
            // if (coordinates.length === 0) {
            //     polyline.editor.startEditing();
            //     polyline.editor.startDrawing();
            // }

            polyline.events.add([
                'geometrychange',
            ], () => {
                polyline.properties.set('hintContent', polyline.geometry.getDistance().toFixed(2) + ' м')
            });

            polyline.events.add([
                // 'mapchange',
                // 'geometrychange',
                // 'pixelgeometrychange',
                // 'optionschange',
                // 'propertieschange',
                // 'balloonopen',
                // 'balloonclose',
                // 'hintopen',
                // 'hintclose',
                // 'dragstart',
                // 'dragend',
                'click',
                // 'contextmenu',
            ], function (e) {
                e.stopPropagation()

                // const points = polyline.properties.get('points');
                // const firstPoint = points[0]
                // const secondPoint = points[1]

                ShowLineInfoBar()

                firstPointName.innerHTML = 'Колодец #' + startPoint.properties.get('pointId')
                secondPointName.innerHTML = 'Колодец #' + endPoint.properties.get('pointId')
                lineLength.innerHTML = polyline.properties.get('hintContent')

                currentLine = polyline
            });

            allPolyLines.push(polyline)

            return polyline;
        }

        // function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
        //     const R = 6371; // Radius of the earth in km
        //     const dLat = deg2rad(lat2-lat1);  // deg2rad below
        //     const dLon = deg2rad(lon2-lon1);
        //     const a =
        //         Math.sin(dLat/2) * Math.sin(dLat/2) +
        //         Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        //         Math.sin(dLon/2) * Math.sin(dLon/2)
        //     ;
        //     const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        //     const d = R * c; // Distance in km
        //     return d;
        // }
        //
        // function deg2rad(deg) {
        //     return deg * (Math.PI/180)
        // }
        //
        // // const distanceMOWBKK = getDistanceFromLatLonInKm(
        // //     55.45, 37.36, 13.45, 100.30
        // // )
        // //
        // // console.log(distanceMOWBKK);
        //
        // function getDistanceByCoordinatesArray(coordinates) {
        //     let name = 'no calculate'
        //     if (coordinates.length === 2) {
        //         name = getDistanceFromLatLonInKm(
        //             coordinates[0][0],
        //             coordinates[0][1],
        //             coordinates[1][0],
        //             coordinates[1][1],
        //         )
        //         name = (name * 1000).toFixed(3) + ' м'
        //     }
        //     return name;
        // }

        // function saveNewLine(polyLineCoordinates, name) {
        //     let polyLines = localStorage.getItem('polyLines')
        //     if (polyLines === null) {
        //         polyLines = {};
        //     } else {
        //         polyLines = JSON.parse(polyLines)
        //     }
        //     polyLines[name] = polyLineCoordinates
        //     localStorage.setItem('polyLines', JSON.stringify(polyLines))
        // }
        //
        // function buildOldPolyLines() {
        //
        //     let polyLines = localStorage.getItem('polyLines')
        //     if (polyLines === null) {
        //         return
        //     } else {
        //         polyLines = JSON.parse(polyLines)
        //     }
        //
        //     Object.keys(polyLines).forEach((name) => {
        //         newPolyline(polyLines[name], name)
        //     });
        // }
        //
        // function NewPolyLineModalWindow() {
        //     const modalWindowContainer = CreateElement('div', {});
        //
        //     const nameFieldContainer = CreateElement('div', {}, modalWindowContainer);
        //     const nameFieldLabel = CreateElement('label', {content: 'Название', class: 'in-input'}, nameFieldContainer);
        //     const nameField = CreateElement('input', {attr: {type: 'text'}}, nameFieldContainer);
        //
        //     const addButton = CreateElement('button', {content: 'добавить', attr: {style: 'margin-top: 10px;'}}, modalWindowContainer);
        //     addButton.addEventListener('click', () => {
        //         if (nameField.value.length === 0) {
        //             return
        //         }
        //         newPolyline([], nameField.value)
        //         CloseModal(modal)
        //     });
        //
        //     const modal = ModalWindow(modalWindowContainer)
        //     nameField.focus()
        // }

        function newLayoutModalWindow() {
            const modalWindowContainer = CreateElement('div', {});

            const titleFieldContainer = CreateElement('div', {}, modalWindowContainer);
            const titleFieldLabel = CreateElement('label', {content: 'Название', class: 'in-input'}, titleFieldContainer);
            const titleField = CreateElement('input', {attr: {type: 'text'}}, titleFieldContainer);

            titleField.addEventListener('keypress', (event) => {
                if (event.key === 'Enter') {
                    thisCreateLayout()
                }
            })

            const addButton = CreateElement('button', {content: 'добавить', attr: {style: 'margin-top: 10px;'}}, modalWindowContainer);
            addButton.addEventListener('click', () => {
                thisCreateLayout()
            });

            function thisCreateLayout() {
                if (titleField.value.length === 0) {
                    return
                }
                requestOnServer("{{route('layout.add')}}", {
                    title: titleField.value,
                    color: getRandomColor()
                }, (result) => {
                    createLayout(result, true)
                })

                CloseModal(modal)
            }

            const modal = ModalWindow(modalWindowContainer)
            titleField.focus()
        }

        function UpdateLatitudeLongitudeByPoint() {
            if (currentPoint) {
                const coordinates1 = getFixedCoordinates(currentPoint.geometry.getCoordinates())
                pointName.innerHTML = 'Колодец #' + currentPoint.properties.get('pointId')
                latitude.innerHTML = coordinates1[0]
                longitude.innerHTML = coordinates1[1]
            } else {
                pointName.innerHTML = '-'
                latitude.innerHTML = '-'
                longitude.innerHTML = '-'
            }
        }

        deletePoint.addEventListener('click', () => {
            if (!currentPoint) return;

            if (!isEditable()) {
                return
            }

            requestOnServer("{{route('point.delete')}}", {
                pointId: currentPoint.properties.get('pointId')
            }, () => {
                document.dispatchEvent(new CustomEvent('delete-point', {
                    detail: {
                        pointId: currentPoint.properties.get('pointId')
                    }
                }))
                myMap.geoObjects.remove(currentPoint);
                lastCoordinates = [];
                currentPoint = undefined;
                UpdateLatitudeLongitudeByPoint()
            })
        });

        deleteLine.addEventListener('click', () => {
            if (!currentLine) return;

            if (!isEditable()) {
                return
            }

            requestOnServer("{{route('lines.delete')}}", {
                lineId: currentLine.properties.get('lineId')
            }, () => {

                let freshPolyLinesArr = [];
                allPolyLines.map((everyPolyline) => {
                    if (currentLine.properties.get('lineId') !== everyPolyline.properties.get('lineId')) {
                        freshPolyLinesArr.push(everyPolyline)
                    }
                })
                allPolyLines = freshPolyLinesArr

                myMap.geoObjects.remove(currentLine);
                currentLine = undefined
            })
        });

        function ShowPointBar() {
            lineInfoBar.hide();
            pointInfoBar.show();
        }

        function ShowLineInfoBar() {
            pointInfoBar.hide();
            lineInfoBar.show();
        }

        // testButton.addEventListener('click', () => {
        //     const event = new CustomEvent('hide-el', {
        //         detail: {
        //             pointId: 1
        //         }
        //     })
        //     document.dispatchEvent(event)
        // })

        function requestOnServer(route, data = {}, success = null, error = null) {
            fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json;charset=utf-8',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            }).then((response) => {
                if (response.ok) {
                    response.json().then(result => success && success(result))
                } else {
                    response.text().then(text => error && error(text))
                }
            })
        }

        // координаты до 12 знаков
        function getFixedCoordinates(coordinates) {
            if (Array.isArray(coordinates[0])) {
                return [
                    [
                        parseFloat(coordinates[0][0]).toFixed(12),
                        parseFloat(coordinates[0][1]).toFixed(12),
                    ],
                    [
                        parseFloat(coordinates[1][0]).toFixed(12),
                        parseFloat(coordinates[1][1]).toFixed(12),
                    ],
                ]
            } else {
                return [
                    parseFloat(coordinates[0]).toFixed(12),
                    parseFloat(coordinates[1]).toFixed(12),
                ]
            }
        }

    </script>
@stop
