<?php
// بيانات الاتصال بقاعدة البيانات
$conn = mysqli_connect("localhost", "root", "", "universitydb");
if (!$conn) {
    die("فشل الاتصال: " . mysqli_connect_error());
}

$msg = "";

// دالة مساعدة عامة لرفع الملفات (صور أو ملفات PDF)
function uploadFile($file_input_name, $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        $file_type = $_FILES[$file_input_name]['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_name = time() . "_" . mt_rand(1000, 9999) . "_" . basename($_FILES[$file_input_name]['name']);
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $target_file)) {
                return $file_name;
            }
        }
    }
    return null;
}

// 1. معالجة إدخال الكورسات الجديدة مع الصورة
if (isset($_POST['add_course'])) {
    $name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $tev = mysqli_real_escape_string($conn, $_POST['course_tev']);
    $seer = intval($_POST['course_seer']);
    
    $image_name = uploadFile('course_image', ['image/jpeg', 'image/png', 'image/jpg']);
    $image_sql_val = $image_name ? "'$image_name'" : "NULL";

    $sql = "INSERT INTO courses (name, tev, seer, image) VALUES ('$name', '$tev', '$seer', $image_sql_val)";
    if (mysqli_query($conn, $sql)) {
        $msg = "تم إضافة الكورس مع الصورة بنجاح!";
    } else {
        $msg = "خطأ: " . mysqli_error($conn);
    }
}

// 2. معالجة تعديل كورس موجود
if (isset($_POST['update_course'])) {
    $old_name = mysqli_real_escape_string($conn, $_POST['old_course_name']);
    $name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $tev = mysqli_real_escape_string($conn, $_POST['course_tev']);
    $seer = intval($_POST['course_seer']);

    $image_name = uploadFile('course_image', ['image/jpeg', 'image/png', 'image/jpg']);
    
    if ($image_name) {
        $update_sql = "UPDATE courses SET name='$name', tev='$tev', seer='$seer', image='$image_name' WHERE name='$old_name'";
    } else {
        $update_sql = "UPDATE courses SET name='$name', tev='$tev', seer='$seer' WHERE name='$old_name'";
    }

    if (mysqli_query($conn, $update_sql)) {
        $msg = "تم تحديث بيانات الكورس بنجاح!";
    } else {
        $msg = "خطأ في التحديث: " . mysqli_error($conn);
    }
}

// 3. معالجة حذف كورس
if (isset($_GET['delete_course'])) {
    $del_c_name = mysqli_real_escape_string($conn, $_GET['delete_course']);
    
    $img_q = mysqli_query($conn, "SELECT image FROM courses WHERE name='$del_c_name'");
    if($img_q && $img_row = mysqli_fetch_assoc($img_q)){
        if(!empty($img_row['image']) && file_exists("uploads/" . $img_row['image'])) {
            unlink("uploads/" . $img_row['image']);
        }
    }

    $del_sql = "DELETE FROM courses WHERE name='$del_c_name'";
    if (mysqli_query($conn, $del_sql)) {
        $msg = "تم حذف الكورس بنجاح!";
    } else {
        $msg = "خطأ في الحذف: " . mysqli_error($conn);
    }
}

// 4. معالجة إدخال الطلاب الجدد مع الصورة وملف الـ PDF (الشهادة)
if (isset($_POST['add_student'])) {
    $s_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $ege = intval($_POST['student_ege']);
    $course_name = mysqli_real_escape_string($conn, $_POST['student_course']);

    $image_name = uploadFile('student_image', ['image/jpeg', 'image/png', 'image/jpg']);
    $image_sql_val = $image_name ? "'$image_name'" : "NULL";

    // رفع ملف الـ PDF الخاص بالشهادة
    $pdf_name = uploadFile('student_certificate', ['application/pdf']);
    $pdf_sql_val = $pdf_name ? "'$pdf_name'" : "NULL";

    $check_course = "SELECT * FROM courses WHERE name = '$course_name'";
    $course_result = mysqli_query($conn, $check_course);

    if (mysqli_num_rows($course_result) > 0) {
        $sql2 = "INSERT INTO students (name, ege, course_name, image, certificate) VALUES ('$s_name', '$ege', '$course_name', $image_sql_val, $pdf_sql_val)";
        if (mysqli_query($conn, $sql2)) {
            $msg = "تم إضافة الطالب مع صورته وشهادة الـ PDF وتسجيله في الكورس بنجاح!";
        } else {
            $msg = "خطأ: " . mysqli_error($conn);
        }
    } else {
        $msg = "⚠️ تنبيه: الكورس غير متوفر! يرجى إضافته أولاً لقائمة الكورسات.";
    }
}

// 5. معالجة تعديل طالب موجود (مع إمكانية تحديث الصورة أو ملف الـ PDF)
if (isset($_POST['update_student'])) {
    $old_s_name = mysqli_real_escape_string($conn, $_POST['old_student_name']);
    $s_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $ege = intval($_POST['student_ege']);
    $course_name = mysqli_real_escape_string($conn, $_POST['student_course']);

    $image_name = uploadFile('student_image', ['image/jpeg', 'image/png', 'image/jpg']);
    $pdf_name = uploadFile('student_certificate', ['application/pdf']);

    $check_course = "SELECT * FROM courses WHERE name = '$course_name'";
    $course_result = mysqli_query($conn, $check_course);

    if (mysqli_num_rows($course_result) > 0) {
        // بناء جملة التحديث بناءً على الملفات المرفوعة حديثاً
        $updates = "name='$s_name', ege='$ege', course_name='$course_name'";
        if ($image_name) {
            $updates .= ", image='$image_name'";
        }
        if ($pdf_name) {
            $updates .= ", certificate='$pdf_name'";
        }

        $update_s_sql = "UPDATE students SET $updates WHERE name='$old_s_name'";

        if (mysqli_query($conn, $update_s_sql)) {
            $msg = "تم تحديث بيانات الطالب بنجاح!";
        } else {
            $msg = "خطأ في التحديث: " . mysqli_error($conn);
        }
    } else {
        $msg = "⚠️ تنبيه: الكورس المراد التعديل إليه غير متوفر في النظام!";
    }
}

// 6. معالجة حذف طالب (مع حذف ملفاته المرتبطة من الخادم)
if (isset($_GET['delete_student'])) {
    $del_s_name = mysqli_real_escape_string($conn, $_GET['delete_student']);
    
    $file_q = mysqli_query($conn, "SELECT image, certificate FROM students WHERE name='$del_s_name'");
    if($file_q && $file_row = mysqli_fetch_assoc($file_q)){
        if(!empty($file_row['image']) && file_exists("uploads/" . $file_row['image'])) {
            unlink("uploads/" . $file_row['image']);
        }
        if(!empty($file_row['certificate']) && file_exists("uploads/" . $file_row['certificate'])) {
            unlink("uploads/" . $file_row['certificate']);
        }
    }

    $del_s_sql = "DELETE FROM students WHERE name='$del_s_name'";
    if (mysqli_query($conn, $del_s_sql)) {
        $msg = "تم حذف الطالب بنجاح!";
    } else {
        $msg = "خطأ في الحذف: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الكورسات والطلاب - الجامعة</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        header h1 {
            color: #00ffcc;
            font-size: 26px;
            margin-bottom: 5px;
        }

        header p {
            color: #b0c4de;
            font-size: 14px;
        }

        .alert {
            background: rgba(0, 255, 204, 0.2);
            border: 1px solid #00ffcc;
            color: #00ffcc;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            width: 100%;
            max-width: 600px;
            font-weight: bold;
        }

        .container {
            display: flex;
            gap: 25px;
            width: 100%;
            max-width: 900px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .card h2 {
            font-size: 20px;
            color: #00ffcc;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #d1d8e0;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            border-radius: 8px;
            font-size: 14px;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #00ffcc;
        }

        button, .action-btn {
            background: linear-gradient(135deg, #00ffcc, #00b388);
            color: #000;
            padding: 12px;
            width: 100%;
            border: none;
            font-weight: bold;
            font-size: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        button:hover, .action-btn:hover {
            opacity: 0.9;
        }

        .edit-btn {
            background: linear-gradient(135deg, #ffaa00, #ff8800);
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-left: 5px;
        }
        .edit-btn:hover { opacity: 0.9; }

        .delete-btn {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }
        .delete-btn:hover { opacity: 0.9; }

        .pdf-link {
            color: #00ffcc;
            text-decoration: underline;
            font-weight: bold;
            font-size: 13px;
        }

        .data-section {
            width: 100%;
            max-width: 900px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            margin-bottom: 25px;
        }

        .data-section h2 {
            font-size: 20px;
            color: #00ffcc;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
        }

        th {
            background: rgba(0, 255, 204, 0.15);
            color: #00ffcc;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .toggle-container {
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
            max-width: 900px;
        }

        .table-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            color: #8fa3ad;
        }
    </style>
</head>
<body>

    <header>
        <h1>نظام إدارة الكورسات والطلاب</h1>
        <p>إعداد وتطوير الطالب المبدع: <strong>كهلان قملان</strong></p>
    </header>

    <?php if(!empty($msg)): ?>
        <div class="alert"><?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- واجهة تعديل الطالب -->
    <?php 
    if (isset($_GET['edit_student'])) {
        $edit_s_name = mysqli_real_escape_string($conn, $_GET['edit_student']);
        $es_query = mysqli_query($conn, "SELECT * FROM students WHERE name = '$edit_s_name'");
        if ($es_query && mysqli_num_rows($es_query) > 0) {
            $es_row = mysqli_fetch_assoc($es_query);
    ?>
            <div class="card" style="max-width: 600px; margin-bottom: 30px; border-color: #ffaa00;">
                <h2 style="color: #ffaa00;">تعديل بيانات الطالب</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="old_student_name" value="<?php echo htmlspecialchars($es_row['name']); ?>">
                    
                    <label>اسم الطالب (name):</label>
                    <input type="text" name="student_name" value="<?php echo htmlspecialchars($es_row['name']); ?>" required>

                    <label>العمر أو الرقم (ege):</label>
                    <input type="number" name="student_ege" value="<?php echo htmlspecialchars($es_row['ege']); ?>" required>

                    <label>اسم الكورس المراد التسجيل به:</label>
                    <input type="text" name="student_course" value="<?php echo htmlspecialchars(isset($es_row['course_name']) ? $es_row['course_name'] : ''); ?>" required>

                    <label>صورة الطالب الجديدة (اختياري):</label>
                    <input type="file" name="student_image" accept="image/*">

                    <label>ملف الشهادة PDF الجديد (اختياري):</label>
                    <input type="file" name="student_certificate" accept="application/pdf">

                    <button type="submit" name="update_student" style="background: linear-gradient(135deg, #ffaa00, #ff8800); color: #fff;">حفظ تعديلات الطالب</button>
                    <a href="?show_data=1" class="action-btn" style="background: rgba(255,255,255,0.1); color:#fff; margin-top:10px;">إلغاء</a>
                </form>
            </div>
    <?php 
        }
    } 
    ?>

    <div class="container">
        <!-- نموذج إدخال الكورسات -->
        <div class="card">
            <h2>إضافة كورس جديد (Courses)</h2>
            <form method="POST" enctype="multipart/form-data">
                <label>اسم الكورس (name):</label>
                <input type="text" name="course_name" required>

                <label>التفاصيل (tev):</label>
                <textarea name="course_tev" rows="3" required></textarea>

                <label>السعر أو المعرف (seer):</label>
                <input type="number" name="course_seer" required>

                <label>صورة الكورس (image):</label>
                <input type="file" name="course_image" accept="image/*">

                <button type="submit" name="add_course">حفظ الكورس</button>
            </form>
        </div>

        <!-- نموذج إدخال الطلاب -->
        <div class="card">
            <h2>إضافة طالب جديد (Students)</h2>
            <form method="POST" enctype="multipart/form-data">
                <label>اسم الطالب (name):</label>
                <input type="text" name="student_name" required>

                <label>العمر أو الرقم (ege):</label>
                <input type="number" name="student_ege" required>

                <label>اسم الكورس المراد التسجيل به:</label>
                <input type="text" name="student_course" placeholder="أدخل اسم كورس موجود مسبقاً" required>

                <label>صورة الطالب (image):</label>
                <input type="file" name="student_image" accept="image/*">

                <label>ملف الشهادة PDF (certificate):</label>
                <input type="file" name="student_certificate" accept="application/pdf">

                <button type="submit" name="add_student">حفظ الطالب</button>
            </form>
        </div>
    </div>

    <!-- زر عرض البيانات والتقارير -->
    <div class="toggle-container">
        <form method="GET">
            <button type="submit" name="show_data" value="1" class="action-btn" style="max-width: 350px;">عرض الكورسات المتوفرة والطلاب المسجلين</button>
        </form>
    </div>

    <?php if (isset($_GET['show_data']) && $_GET['show_data'] == '1'): ?>
        
        <!-- قسم عرض الكورسات المتوفرة -->
        <div class="data-section">
            <h2>قائمة الكورسات المتوفرة في النظام</h2>
            <table>
                <thead>
                    <tr>
                        <th>الصورة</th>
                        <th>اسم الكورس</th>
                        <th>التفاصيل</th>
                        <th>السعر / المعرف</th>
                        <th>التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $courses_query = "SELECT * FROM courses";
                    $courses_result = mysqli_query($conn, $courses_query);

                    if ($courses_result && mysqli_num_rows($courses_result) > 0) {
                        while ($course_row = mysqli_fetch_assoc($courses_result)) {
                            echo "<tr>";
                            $img_path = !empty($course_row['image']) ? "uploads/" . htmlspecialchars($course_row['image']) : "";
                            echo "<td>";
                            if ($img_path && file_exists($img_path)) {
                                echo "<img src='" . $img_path . "' class='table-img' alt='course'>";
                            } else {
                                echo "لا توجد صورة";
                            }
                            echo "</td>";
                            
                            echo "<td>" . htmlspecialchars($course_row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($course_row['tev']) . "</td>";
                            echo "<td>" . htmlspecialchars($course_row['seer']) . "</td>";
                            echo "<td>
                                    <a href='?show_data=1&delete_course=" . urlencode($course_row['name']) . "' class='delete-btn' onclick='return confirm(\"هل أنت متأكد من حذف هذا الكورس؟\");'>حذف</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;'>لا توجد كورسات متاحة حالياً.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- قسم عرض الطلاب المسجلين (مع عرض الصورة وملف الـ PDF) -->
        <div class="data-section">
            <h2>قائمة الطلاب المسجلين والكورسات المرتبطة بهم</h2>
            <table>
                <thead>
                    <tr>
                        <th>الصورة</th>
                        <th>اسم الطالب</th>
                        <th>العمر / الرقم</th>
                        <th>الكورس المسجل به</th>
                        <th>الشهادة (PDF)</th>
                        <th>التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $students_query = "SELECT * FROM students";
                    $students_result = mysqli_query($conn, $students_query);

                    if ($students_result && mysqli_num_rows($students_result) > 0) {
                        while ($student_row = mysqli_fetch_assoc($students_result)) {
                            echo "<tr>";
                            $s_img_path = !empty($student_row['image']) ? "uploads/" . htmlspecialchars($student_row['image']) : "";
                            echo "<td>";
                            if ($s_img_path && file_exists($s_img_path)) {
                                echo "<img src='" . $s_img_path . "' class='table-img' alt='student'>";
                            } else {
                                echo "لا توجد صورة";
                            }
                            echo "</td>";

                            echo "<td>" . htmlspecialchars($student_row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($student_row['ege']) . "</td>";
                            $assigned_course = isset($student_row['course_name']) ? $student_row['course_name'] : 'غير مسجل';
                            echo "<td>" . htmlspecialchars($assigned_course) . "</td>";
                            
                            // عرض رابط ملف الـ PDF (الشهادة)
                            echo "<td>";
                            $pdf_path = !empty($student_row['certificate']) ? "uploads/" . htmlspecialchars($student_row['certificate']) : "";
                            if ($pdf_path && file_exists($pdf_path)) {
                                echo "<a href='" . $pdf_path . "' target='_blank' class='pdf-link'>عرض الشهادة (PDF)</a>";
                            } else {
                                echo "لا توجد شهادة";
                            }
                            echo "</td>";

                            echo "<td>
                                    <a href='?show_data=1&edit_student=" . urlencode($student_row['name']) . "' class='edit-btn'>تعديل</a>
                                    <a href='?show_data=1&delete_student=" . urlencode($student_row['name']) . "' class='delete-btn' onclick='return confirm(\"هل أنت متأكد من حذف هذا الطالب؟\");'>حذف</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>لا توجد بيانات طلاب مسجلة حالياً.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <footer>
        <p>&copy; 2026 جميع الحقوق محفوظة - كهلان قملان</p>
    </footer>

</body>
</html>

<?php
mysqli_close($conn);
?>