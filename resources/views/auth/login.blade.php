@extends('app')

@section('style')
    <style>

        body {
            margin: 0;
        }

        button {
            width: 100%;
            cursor: pointer;
            padding: 10px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .main-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100vw;
            height: 100vh;
        }
    </style>
@stop

@section('content')
    <div class="main-container">

        <div>
            <div class="mb-10">
                <label class="in-input">Логин</label>
                <input id="login" type="text">
            </div>
            <div class="mb-10">
                <label class="in-input">Пароль</label>
                <input id="password" type="password">
            </div>
            <div>
                <button id="loginButton">Войти</button>
            </div>
        </div>

    </div>
@stop

@section('js')
    <script>

        loginButton.addEventListener('click', () => {

            if (login.value.length === 0 || password.value.length === 0) {
                return
            }

            fetch("{{route('login')}}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json;charset=utf-8',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    login: login.value,
                    password: password.value,
                })
            }).then((response) => {
                if (response.ok) {
                    location.reload()
                } else {
                    response.text().then(text => alert(text))
                }
            })
        })



    </script>
@stop
