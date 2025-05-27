<?php
// app/core/Controller.php

abstract class Controller {
    /**
     * Load model
     * @param string $model Name of the model class
     * @return object|null Instance of the model or null on failure
     */
    protected function model($model) {
        // Path to the model file
        $modelPath = '../app/models/' . $model . '.php';
        // Check if file exists
        if (file_exists($modelPath)) {
            require_once $modelPath;
            // Check if class exists
            if (class_exists($model)) {
                return new $model();
            } else {
                // Log error and stop execution if class is not found
                error_log("Controller Error: Model class '{$model}' not found in file '{$modelPath}'.");
                die('Critical Error: Model class ' . $model . ' could not be loaded.');
            }
        } else {
            // Log error and stop execution if file is not found
            error_log("Controller Error: Model file '{$modelPath}' not found.");
            die('Critical Error: Model file ' . $modelPath . ' could not be loaded.');
        }
    }

    /**
     * Load view
     * @param string $view Name of the view file (e.g., 'pages/index')
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function view($view, $data = []) {
        // Extract data for easy access in the view
        extract($data);

        $viewPath = '../app/views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            // Check if a specific layout is requested for this view
            if (isset($data['layout']) && $data['layout'] === 'print') {
                // If 'print' layout is requested, load only the view content
                // This is useful for printable pages like invoices, receipts, etc.
                require_once $viewPath;
            } else {
                // Otherwise, load the standard header, view, and footer
                $headerPath = '../app/views/layouts/header.php';
                $footerPath = '../app/views/layouts/footer.php';

                if (file_exists($headerPath)) {
                    require_once $headerPath;
                } else {
                    // Log a warning if the header file is missing, but continue
                    error_log("Controller Warning: Header file not found at: " . $headerPath . " for view: " . $view);
                }

                require_once $viewPath; // Load the main view content

                if (file_exists($footerPath)) {
                    require_once $footerPath;
                } else {
                    // Log a warning if the footer file is missing
                    error_log("Controller Warning: Footer file not found at: " . $footerPath . " for view: " . $view);
                }
            }
        } else {
            // If the view file does not exist, show a 404 error
            // Ensure ErrorController and its notFound method exist
            error_log("Controller Error: View file not found at: " . $viewPath);
            if (file_exists('../app/controllers/ErrorController.php')) {
                require_once '../app/controllers/ErrorController.php';
                if (class_exists('ErrorController')) {
                    $errorController = new ErrorController();
                    if (method_exists($errorController, 'notFound')) {
                        $errorController->notFound("فایل ویو یافت نشد: " . $view);
                        exit; // Stop further execution
                    }
                }
            }
            // Fallback if ErrorController is not available
            die('Critical Error: View file ' . $viewPath . ' not found, and ErrorController is unavailable.');
        }
    }
}
?>
