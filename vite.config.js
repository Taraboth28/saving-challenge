export default defineConfig({
    // ... existing plugins and resolve options
    build: {
        outDir: 'dist',
        manifest: true,
        rollupOptions: {
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
        },
    },
});