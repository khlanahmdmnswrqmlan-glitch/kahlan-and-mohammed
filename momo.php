<?php
// بيانات الاتصال بقاعدة البيانات
$conn = mysqli_connect("localhost", "root", "", "universitydb");
if (!$conn) {
    die("فشل الاتصال: " . mysqli_connect_error());
}

$msg = "";

// معالجة إدخال الكورسات
if (isset($_POST['add_course'])) {
    $name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $tev = mysqli_real_escape_string($conn, $_POST['course_tev']);
    $seer = intval($_POST['course_seer']);

    $sql = "INSERT INTO courses (name, tev, seer) VALUES ('$name', '$tev', '$seer')";
    if (mysqli_query($conn, $sql)) {
        $msg = "✨ تم إضافة الكورس بنجاح!";
    } else {
        $msg = "خطأ: " . mysqli_error($conn);
    }
}

// معالجة إدخال الطلاب
if (isset($_POST['add_student'])) {
    $s_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $ege = intval($_POST['student_ege']);

    $sql2 = "INSERT INTO students (name, ege) VALUES ('$s_name', '$ege')";
    if (mysqli_query($conn, $sql2)) {
        $msg = "✨ تم إضافة الطالب بنجاح!";
    } else {
        $msg = "خطأ: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شاشة إدخال البيانات - محمد صادق الساده </title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Cairo', Tahoma, sans-serif;
        }

        body {
            /* خلفية فخمة بلون أسود كربوني مع تدرجات رمادية وخطوط خفيفة */
            background: radial-gradient(circle at top, #1e1e2f, #0d0d14);
            color: #f5f6fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 40px 20px;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header h1 {
            color: #fbc531; /* لون ذهبي راقي */
            font-size: 28px;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        header p {
            color: #dcdde1;
            font-size: 14px;
        }

        header p strong {
            color: #00cec9;
        }

        .alert {
            background: rgba(251, 197, 49, 0.15);
            border: 1px solid #fbc531;
            color: #fbc531;
            padding: 12px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            width: 100%;
            max-width: 650px;
            font-weight: bold;
        }

        .container {
            display: flex;
            gap: 30px;
            width: 100%;
            max-width: 950px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 30px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: rgba(251, 197, 49, 0.3);
        }

        .card h2 {
            font-size: 20px;
            color: #00cec9; /* لون سماوي جذاب للبطاقات */
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 12px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            color: #b2bec3;
            font-weight: 600;
        }

        input, textarea {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 18px;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #fbc531;
            box-shadow: 0 0 10px rgba(251, 197, 49, 0.2);
        }

        button {
            background: linear-gradient(135deg, #fbc531, #e1b12c);
            color: #2f3640;
            padding: 12px;
            width: 100%;
            border: none;
            font-weight: bold;
            font-size: 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: filter 0.3s ease;
        }

        button:hover {
            filter: brightness(1.1);
        }

        footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            color: #718093;
        }
    </style>
</head>
<body>

    <header>
        <h1>نظام إدارة الكورسات والطلاب</h1>
        <p>إعداد وتطوير المهندس: <strong>محمد عبد الجبار</strong></p>
    </header>

    <?php if(!empty($msg)): ?>
        <div class="alert"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="container">
        <!-- نموذج إدخال الكورسات -->
        <div class="card">
            <h2>إضافة كورس جديد (Courses)</h2>
            <form method="POST">
                <label>اسم الكورس (name):</label>
                <input type="text" name="course_name" placeholder="أدخل اسم الكورس..." required>

                <label>التفاصيل (tev):</label>
                <textarea name="course_tev" rows="3" placeholder="أدخل تفاصيل الكورس..." required></textarea>

                <label>السعر أو المعرف (seer):</label>
                <input type="number" name="seer" placeholder="أدخل القيمة الرقمية..." required>

                <button type="submit" name="add_course">حفظ الكورس في القاعدة</button>
            </form>
        </div>

        <!-- نموذج إدخال الطلاب -->
        <div class="card">
            <h2>إضافة طالب جديد (Students)</h2>
            <form method="POST">
                <label>اسم الطالب (name):</label>
                <input type="text" name="student_name" placeholder="أدخل اسم الطالب..." required>

                <label>العمر أو الرقم (ege):</label>
                <input type="number" name="student_ege" placeholder="أدخل العمر أو الرقم..." required>

                <button type="submit" name="add_student">حفظ الطالب في القاعدة</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 جميع الحقوق محفوظة - تصميم وتطوير محمد عبد الجبار</p>
    </footer>

</body>
</html>

<?php
mysqli_close($conn);
?>