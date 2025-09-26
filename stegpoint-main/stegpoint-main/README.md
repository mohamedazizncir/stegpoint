# StegPoint

**StegPoint** is a desktop application for STEG Sousse employee attendance management.  
It is built mainly with PHP and runs locally using [PHP Desktop](https://github.com/cztomczak/phpdesktop), providing an easy-to-use interface for importing attendance data, generating reports, and managing time tracking — all without needing a separate web server or browser.

---

## Features

- Import daily attendance files (Excel/CSV) automatically
- View and filter attendance records
- Generate monthly attendance reports with overtime and absences calculation
- Export reports in CSV and PDF formats
- User-friendly desktop interface powered by PHP Desktop
- Portable app — no installation required beyond unpacking

---

## Screenshots

<img width="975" height="730" alt="image" src="https://github.com/user-attachments/assets/2972d61a-9358-4bd1-9edc-0c3628517fa2" />
<img width="978" height="726" alt="image" src="https://github.com/user-attachments/assets/a914efc5-a978-4dd8-962b-189420ae44ab" />
<img width="1918" height="1015" alt="image" src="https://github.com/user-attachments/assets/735d69e3-be6c-4bd8-b245-91422ca3bd4d" />
<img width="1918" height="1017" alt="image" src="https://github.com/user-attachments/assets/2be5d741-d99b-4741-979a-efd5d847d61b" />

---

## Installation

1. Download the latest release of [PHP Desktop Chrome](https://github.com/cztomczak/phpdesktop/releases).
2. Extract the archive to a folder of your choice.
3. Replace the `www` folder contents with your StegPoint project files.
4. Edit the `settings.json` file to set your starting page (e.g., `"index_files": ["dashboard.php"]`).
5. (Optional) Add your custom icon by placing an `.ico` file in the root folder and updating the `"icon"` property in `settings.json`.
6. Launch the application by running `phpdesktop-chrome.exe`.

---

## Usage

- Modify your PHP files using your favorite IDE (e.g., VS Code).
- Use symbolic links or copy your project files to the `www` folder in PHP Desktop to reflect changes.
- Import attendance files as needed; the database updates automatically.
- Generate and export attendance reports via the UI.

---

## Requirements

- Windows 7 or higher
- PHP Desktop Chrome (bundled PHP 7.x and Chromium)
- (Optional) Microsoft Excel or compatible software for preparing attendance files

---

## Development

If you want to contribute or customize:

- Use symbolic links for easier development synchronization:
  ```cmd
  rmdir "C:\phpdesktop-stegrun\www" /s /q
  mklink /D "C:\phpdesktop-stegrun\www" "C:\xampp\htdocs\stegpoint"
