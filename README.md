
# 📋 SOP: Install LHG Activity Log Plugin on a WordPress Website

**Document Version:** 1.0  
**Last Updated:** April 21, 2025  
**Author:** Raju at LHG 

---

## 🎯 Purpose

This SOP describes the step-by-step procedure to install and activate the **LHG Activity Log Plugin** from the GitHub repository on any WordPress website.

---

## 📦 Requirements

- Access to the target WordPress website admin (`/wp-admin`)
- Administrator role on WordPress
- GitHub access to: [https://github.com/lhgdevelopment/wp-lhg-activity-log](https://github.com/lhgdevelopment/wp-lhg-activity-log)
- (Optional) FTP/SFTP access or cPanel access if uploading via file manager

---

## 🔧 Steps to Install

### 1. Download the Plugin Files
- Go to the repository:  
  ➔ [https://github.com/lhgdevelopment/wp-lhg-activity-log](https://github.com/lhgdevelopment/wp-lhg-activity-log)
- Click the **green "Code" button**.
- Select **Download ZIP**.
- Save the ZIP file to your local machine.

---

### 2. Upload the Plugin to WordPress

#### Option A: Upload via WordPress Admin
- Log into the WordPress Admin Dashboard (`yourdomain.com/wp-admin`).
- Go to **Plugins > Add New**.
- Click **Upload Plugin** at the top.
- Click **Choose File** and select the ZIP file you downloaded.
- Click **Install Now**.
- After installation, click **Activate Plugin**.

#### Option B: Upload via FTP (alternative method)
- Unzip the downloaded ZIP file on your local computer.
- Use an FTP client (like FileZilla) to connect to your website’s server.
- Navigate to `/wp-content/plugins/`.
- Upload the entire **`wp-lhg-activity-log`** folder to this directory.
- After uploading, go to **Plugins** in WordPress Admin and click **Activate** under “LHG Activity Log”.

---

### 3. Verify Installation
- After activation, you should see a new menu item or settings area for the **LHG Activity Log** under the WordPress Dashboard (location may vary depending on plugin settings).
- Confirm the plugin is working by performing some activities (e.g., editing a page) and checking if logs are generated.
