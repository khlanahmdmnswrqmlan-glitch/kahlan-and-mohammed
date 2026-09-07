<?php
// بيانات الاتصال بقاعدة البيانات
$host = "localhost";
$username = "root";
$password = "";
$dbname = "universitydb";

// إنشاء الاتصال
$conn = mysqli_connect($host, $username, $password, $dbname);

// التحقق من الاتصال
if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

// جلب بيانات الكورسات من الجداول
$sql = "SELECT * FROM courses";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة الكورسات - نظام إدارة الجامعة</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            /* خلفية قوية ومتدرجة وعصرية */
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
        }

        header h1 {
            font-size: 28px;
            color: #00ffcc;
            margin-bottom: 5px;
        }

        header p {
            font-size: 14px;
            color: #b0c4de;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            overflow: hidden;
            border-radius: 8px;
        }

        th, td {
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: rgba(0, 255, 204, 0.2);
            color: #00ffcc;
            font-weight: bold;
            font-size: 16px;
        }

        tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: background 0.3s ease;
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.08);
        }

        td {
            color: #e0e0e0;
            font-size: 15px;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #ff6b6b;
            font-size: 16px;
        }

        footer {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #8fa3ad;
        }
    </style>
</head>
<body>

    <div class="container">
        <header>
            <h1>قائمة الكورسات المتاحة</h1>
            <p>إعداد وتطوير الطالب: <strong>كهلان قملان</strong></p>
        </header>

        <table>
            <thead>
                <tr>
                    <th>رقم الكورس (ID)</th>
                    <th>اسم الكورس</th>
                    <th>التفاصيل</th>
                    <th>السعر</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    // عرض بيانات كل كورس داخل جدول الويب
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>"; // تأكد من اسم عمود الـ ID في جدول الكورسات لديك
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['details']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['price']) . " \$</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='no-data'>لا توجد كورسات مضافة حالياً في قاعدة البيانات.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; 2026 نظام إدارة الكورسات الجامعية</p>
    </footer>

</body>
</html>

<?php
// إغلاق الاتصال
mysqli_close($conn);
?>