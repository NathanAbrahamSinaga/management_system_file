<?php
// Language handler improve v1.0.1
class Language
{
    private $lang = 'en';
    private $translations = array();

    public function __construct()
    {
        $this->setLanguage();
        $this->loadTranslations();
    }

    private function setLanguage()
    {
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'id'])) {
            $_SESSION['language'] = $_GET['lang'];
        }

        $this->lang = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
    }

    private function loadTranslations()
    {
        $file = __DIR__ . '/../languages/' . $this->lang . '.php';
        if (file_exists($file)) {
            include $file;
            $this->translations = $translations;
        }
    }

    public function get($key)
    {
        return isset($this->translations[$key]) ? $this->translations[$key] : $key;
    }

    public function getCurrentLang()
    {
        return $this->lang;
    }
}

// Global function to get translation
function __($key)
{
    global $language;
    return $language->get($key);
}

// Initialize language
$language = new Language();
?>