import app from './app.js';
import config from './config.js';

const PORT = config.app.port || 8080;

const server = app.listen(PORT, () => {
    console.log(`=======================================================`);
    console.log(` Vaishnavi Tours Node.js Server Started Successfully!`);
    console.log(` URL: http://localhost:${PORT}`);
    console.log(` Environment: ${config.app.env}`);
    console.log(` Database: SQLite WAL Connected`);
    console.log(`=======================================================`);
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\nGracefully shutting down Node.js server...');
    server.close(() => {
        console.log('Server closed. Exiting process.');
        process.exit(0);
    });
});

process.on('SIGTERM', () => {
    console.log('\nReceived SIGTERM. Shutting down...');
    server.close(() => {
        process.exit(0);
    });
});
