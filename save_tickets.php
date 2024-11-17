<?php

// Параметры подключения
$host = 'localhost'; // Хост
$username = 'root'; // Имя пользователя
$password = ''; // Пароль
$database = 'test_nevatrip'; // Имя базы данных

// Подключение к MySQL
$conn = mysqli_connect($host, $username, $password);

if (!$conn) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}

// Выбор базы данных
if (!mysqli_select_db($conn, $database)) {
    die('Ошибка выбора базы данных: ' . mysqli_error($conn));
}


function curl_api($url, $method = 'GET', $param = null) {
    // Инициализация cURL
    $ch = curl_init();

    // Настройка URL
    curl_setopt($ch, CURLOPT_URL, $url);

    // Установка заголовков
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json"
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Метод запроса
    $method = strtoupper($method);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    // Если есть параметры, добавляем их
    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
    }

    // Общие настройки
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Отключение проверки сертификата
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Возврат результата вместо вывода

    // Выполнение запроса
    $response = curl_exec($ch);

    // Проверка ошибок
    if ($response === false) {
        $error = curl_error($ch);

        // Логирование ошибки
        error_log("[cURL Error] URL: $url | Method: $method | Error: $error");

        // Закрытие cURL и возврат ошибки
        curl_close($ch);
        die($error);
        // return [
        //     'success' => false,
        //     'error' => $error
        // ];
    }

    // Закрытие cURL
    curl_close($ch);

    // Декодируем ответ (если это JSON)
    $decodedResponse = json_decode($response, true);

    // Логирование ответа 
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("[Response Error] URL: $url | Response: $response | Error: Invalid JSON");
        die("Invalid JSON response");
        // return [
        //     'success' => false,
        //     'error' => 'Invalid JSON response',
        //     'raw_response' => $response
        // ];
    }

    // Возвращаем успешный результат
    return $decodedResponse;
}



function add_order($conn, $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $try_count = 0) {
    if ($try_count > 6) { // Кол-во попыток в рекурсии
        die("Кол-во попыток забронировать билеты исчерпано, попробуйте позже");
    }
    
    // Получаем текущий временной штамп
    $current_timestamp = time();

    // Составляем строку на основе входных данных и временного штампа
    $inputString = $event_id . $event_date . $ticket_adult_price . $ticket_adult_quantity . $ticket_kid_price . $ticket_kid_quantity . $current_timestamp;
    
    // Генерируем случайное число
    $randomNumber = mt_rand(10000, 99999);
    
    // Хэшируем строку для уникальности
    $hash = substr(md5($inputString . $randomNumber), 0, 10); // Обрезаем до 10 символов
    
    // Соединяем хэш с случайным числом
    $barcode = $hash . $randomNumber;

    // Можно преобразовать хэш в числовой формат
    $barcode = preg_replace('/\D/', '', $hash) . $randomNumber;
    $barcode = substr($barcode, 0, 15); // Урезаем до 15 цифр

    $param = [
        'event_id' => $event_id,
        'event_date' => $event_date,
        'ticket_adult_price' => $ticket_adult_price,
        'ticket_adult_quantity' => $ticket_adult_quantity,
        'ticket_kid_price' => $ticket_kid_price,
        'ticket_kid_quantity' => $ticket_kid_quantity,
        'barcode' => $barcode
    ];

    $response = curl_api("https://api.site.com/book", "POST", $param);

    if ($response['error'] === 'barcode already exists') { //Вызываем рекурсивно эту же функцию, не больше 5 раз
        add_order($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $try_count+1);
        
    } else if ($response['message'] === 'order successfully booked') { //Подтверждаем бронь
        $param_approve = [
            'barcode' => $barcode
        ];
        $response_approve = curl_api("https://api.site.com/approve", "POST", $param_approve);
        if (isset($response_approve['message']) && $response_approve['message'] === 'order successfully aproved') { //Что-то пошло не так
            // Подготовленный запрос
            $query = "INSERT INTO tb_tickets 
            (event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity, barcode, equal_price, created)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // Подготовка запроса
            $stmt = mysqli_prepare($conn, $query);

            if ($stmt === false) {
                die("Ошибка подготовки запроса: " . mysqli_error($conn));
            }

            $equal_price = ($ticket_adult_price * $ticket_adult_quantity) + ($ticket_kid_price * $ticket_kid_quantity);

            // Привязываем параметры
            mysqli_stmt_bind_param(
                $stmt,
                "isiiiisis", // Типы данных: i - integer, s - string
                $event_id,
                $event_date,
                $ticket_adult_price,
                $ticket_adult_quantity,
                $ticket_kid_price,
                $ticket_kid_quantity,
                $barcode,
                $equal_price,
                $current_timestamp
            );

            // Выполнение запроса
            if (mysqli_stmt_execute($stmt)) {
                echo "Данные успешно вставлены!";
            } else {
                error_log("Ошибка выполнения запроса: " . mysqli_stmt_error($stmt));
                die("Ошибка выполнения запроса: " . mysqli_stmt_error($stmt));
            }

            // Закрытие подготовленного запроса и соединения
            mysqli_stmt_close($stmt);
            mysqli_close($conn);


        } else {
            die($response_approve['error']);
        }
    } else {
        die("Какой-то другой ответ от API, не предусмотрен ТЗ");
    }
    
}

?>