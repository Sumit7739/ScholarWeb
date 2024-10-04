const { app, BrowserWindow } = require('electron');
const path = require('path');

let mainWindow;

app.on('ready', () => {
  mainWindow = new BrowserWindow({
    width: 800,
    height: 600,
    webPreferences: {
      nodeIntegration: true,
    },
    icon: path.join(__dirname, 'assets', 'icon.png')  // Set icon path here
  });

  mainWindow.loadURL('https://sumit11.serv00.net/');
});
