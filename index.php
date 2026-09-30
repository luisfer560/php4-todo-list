<?php 
require_once "Database.php";
require_once "Task.php";

// Instanciar la base de datos  y obtener la conexion
$database = new Database();
$db = $database->getConnection();

// Instanciar el objeto task
$task = new Task($db);

// procesar acciones (POST/GET)
if ($_SERVER["REQUEST_METHOD"]== "POST" && isset($_POST["action"])) {
    if ($_POST["action"]== "add" && !empty($_POST["title"])) {
        $task->title = $_POST["title"];
        $task->create();
    }
}

if (isset($_GET["action"])) {
    if ($_GET["action"]== "toggle") {
        $task->id = $_GET["id"];
        $task->completed = $_GET["status"] =="1" ? 0 : 1;
        $task->toggleComplete();
    }elseif ($_GET["action"]== "delete") {
        $task->id = $_GET["id"];
        $task->delete();
    }

    //redirigir para  limpiar el URL
    header("Location: index.php");
    exit();
}

// obtener todas las tareas para listarlas
$stmt = $task->readAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de tarea POO</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-body: #f3f4f6;
            --bg-card: #ffffff;
            --text-main: #1f2937;
            --text-muted: #9ca3af;
            --success: #10b981;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --border: #e5e7eb;
            --radius: 12px;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: var(--bg-card);
            width: 100%;
            max-width: 550px;
            padding: 32px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        header {
            margin-bottom: 24px;
        }

        header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-main);
        }

        header p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Formulario */
        .task-form {
            display: flex;
            gap: 10px;
            margin-bottom: 24px;
        }

        .task-form input[type="text"] {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .task-form input[type="text"]:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        /* Lista de tareas */
        .task-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background-color: #fafafa;
            border: 1px solid var(--border);
            border-radius: 8px;
            transition: border-color 0.2s, transform 0.1s;
        }

        .task-item:hover {
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .task-title {
            font-size: 0.95rem;
            font-weight: 500;
            word-break: break-word;
        }

        .task-item.is-completed .task-title {
            text-decoration: line-through;
            color: var(--text-muted);
        }

        /* Acciones / Botones */
        .task-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-action {
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-toggle {
            background-color: #e0e7ff;
            color: var(--primary);
        }

        .btn-toggle:hover {
            background-color: #c7d2fe;
        }

        .btn-toggle.done {
            background-color: #d1fae5;
            color: #065f46;
        }

        .btn-toggle.done:hover {
            background-color: #a7f3d0;
        }

        .btn-delete {
            background-color: #fee2e2;
            color: var(--danger);
        }

        .btn-delete:hover {
            background-color: #fca5a5;
            color: var(--danger-hover);
        }

        .empty-state {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            padding: 20px 0;
        }
    </style>
</head>
<body>

    <div class="container">
        <header>
            <h1>Gesto de tareas</h1>
            <p>proyecto Poo con php mysql</p>
        </header>
        <!-- Formulario para agregar tarea -->
        <form class="task-form" action="index.php" method="POST">
            <input type="hidden" name="action" value="add">
            <input type="text" name="title" placeholder="que tarea tienes pendiente?" required autocomplete="off">

            <button type="submit" class="btn-primary">agregar</button>
        </form>
        <!--Listado de tarea -->
        <ul class="task-list">
            <?php 
            $hasTasks = false;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                $hasTasks = true;
            ?>
            <li class="task-item <?= $row['completed']? 'is-completed' : '' ?>">
                <span class="task-title">
                    <?= htmlspecialchars($row['title']) ?>
                </span>
                <div class="task-actions">
                    <a href="index.php?action=toggle&id=<?= $row['id']?>&status=<?= $row['completed'] ?>"
                    class="btn-action btn-toggle <?= $row['completed'] ? 'done' : '' ?>">
                        <?= $row['completed'] ? 'Desmarcar' : 'Completar' ?>
                    </a>
                    <a href="index.php?action=delete&id=<?= $row['id']?>"
                    class="btn-action btn-delete"
                    onclick="return confirm('seguro que deseas eliminar esta tarea?')">
                        Eliminar
                    </a>
                </div>
            </li>
            <?php endwhile; ?>

            <?php if (!$hasTasks): ?>
                <div class="empty-state">
                    no hay tareas registrada. Agregar la primera
                </div>
            <?php endif; ?>
        </ul>
    </div>

</body>
</html>