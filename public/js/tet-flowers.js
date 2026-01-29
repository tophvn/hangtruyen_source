/**
 * Hiệu ứng hoa mai và hoa đào rơi - Phong cách Tết
 * Sử dụng: Include file này vào HTML và gọi initTetFlowers()
 */

(function() {
    'use strict';

    let flowers = [];
    let animationId = null;
    let isRunning = false;
    let overlayElement = null;
    let maiImage = null;
    let daoImage = null;

    // Cấu hình
    const config = {
        flowerTypes: [
            { name: 'mai', color: '#FFD700', symbol: '🌸', size: 8 }, // Hoa mai - vàng
            { name: 'dao', color: '#FF69B4', symbol: '🌺', size: 8 }  // Hoa đào - hồng
        ],
        maxFlowers: 60,
        spawnRate: 800, // milliseconds
        windStrength: 0.3,
        gravity: 0.04,
        rotationSpeed: 0.02,
        imagePaths: {
            mai: 'hoamai.png',
            dao: 'hoadao.png'
        }
    };

    // Tạo overlay mờ nhẹ
    function createOverlay() {
        if (overlayElement) return overlayElement;
        
        overlayElement = document.createElement('div');
        overlayElement.id = 'tet-flowers-overlay';
        overlayElement.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.05);
            pointer-events: none;
            z-index: 9998;
        `;
        document.body.appendChild(overlayElement);
        return overlayElement;
    }

    // Tạo hình hoa mai và hoa đào ở góc
    function createCornerFlowers() {
        // Hoa mai góc trên trái
        if (!maiImage && config.imagePaths.mai) {
            maiImage = document.createElement('img');
            maiImage.id = 'tet-mai-corner';
            maiImage.src = config.imagePaths.mai;
            maiImage.alt = 'Hoa Mai';
            maiImage.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                max-width: 150px;
                max-height: 150px;
                width: auto;
                height: auto;
                pointer-events: none;
                z-index: 10000;
                opacity: 0.9;
            `;
            document.body.appendChild(maiImage);
        }

        // Hoa đào góc trên phải
        if (!daoImage && config.imagePaths.dao) {
            daoImage = document.createElement('img');
            daoImage.id = 'tet-dao-corner';
            daoImage.src = config.imagePaths.dao;
            daoImage.alt = 'Hoa Đào';
            daoImage.style.cssText = `
                position: fixed;
                top: 0;
                right: 0;
                max-width: 150px;
                max-height: 150px;
                width: auto;
                height: auto;
                pointer-events: none;
                z-index: 10000;
                opacity: 0.9;
            `;
            document.body.appendChild(daoImage);
        }
    }

    // Xóa hình hoa góc
    function removeCornerFlowers() {
        if (maiImage) {
            maiImage.remove();
            maiImage = null;
        }
        if (daoImage) {
            daoImage.remove();
            daoImage = null;
        }
        if (overlayElement) {
            overlayElement.remove();
            overlayElement = null;
        }
    }

    // Tạo canvas
    function createCanvas() {
        const canvas = document.createElement('canvas');
        canvas.id = 'tet-flowers-canvas';
        canvas.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        `;
        document.body.appendChild(canvas);
        return canvas;
    }

    // Lớp Flower
    class Flower {
        constructor(canvas) {
            this.canvas = canvas;
            this.ctx = canvas.getContext('2d');
            const type = config.flowerTypes[Math.floor(Math.random() * config.flowerTypes.length)];
            this.type = type;
            this.x = Math.random() * canvas.width;
            this.y = -20;
            this.size = type.size + Math.random() * 4; // Hoa nhỏ hơn
            this.speedY = 0.1 + Math.random() * 0.3; // Rơi cực chậm
            this.speedX = (Math.random() - 0.5) * config.windStrength;
            this.rotation = Math.random() * Math.PI * 2;
            this.rotationSpeed = (Math.random() - 0.5) * config.rotationSpeed;
            this.opacity = 0.6 + Math.random() * 0.4;
            this.hue = type.name === 'mai' ? 45 + Math.random() * 10 : 320 + Math.random() * 20;
            this.wobblePhase = Math.random() * Math.PI * 2; // Pha dao động
            this.wobbleSpeed = 0.02 + Math.random() * 0.03; // Tốc độ dao động
            this.wobbleAmplitude = 1 + Math.random() * 1.5; // Biên độ dao động
            this.time = 0; // Thời gian để tạo chuyển động tự nhiên
        }

        update() {
            this.time += 0.016; // ~60fps
            this.y += this.speedY;
            
            // Dao động tự nhiên theo gió (sway)
            this.wobblePhase += this.wobbleSpeed;
            const sway = Math.sin(this.wobblePhase) * this.wobbleAmplitude;
            this.x += this.speedX + sway * 0.3 + Math.sin(this.y * 0.008) * 0.8;
            
            // Xoay tự nhiên với tốc độ thay đổi
            this.rotation += this.rotationSpeed + Math.sin(this.time) * 0.01;
            
            // Tăng tốc nhẹ khi rơi (gravity effect) - cực nhẹ
            this.speedY += config.gravity * 0.02;
            
            // Xóa hoa khi ra khỏi màn hình
            if (this.y > this.canvas.height + 20) {
                return false;
            }
            return true;
        }

        draw() {
            this.ctx.save();
            this.ctx.translate(this.x, this.y);
            this.ctx.rotate(this.rotation);
            this.ctx.globalAlpha = this.opacity;
            
            // Vẽ hoa mai (5 cánh)
            if (this.type.name === 'mai') {
                this.drawMaiFlower();
            } 
            // Vẽ hoa đào (5 cánh)
            else {
                this.drawDaoFlower();
            }
            
            this.ctx.restore();
        }

        drawMaiFlower() {
            const ctx = this.ctx;
            const size = this.size;
            
            // Vẽ cánh hoa mai (màu vàng) - nhỏ và mỏng hơn
            ctx.fillStyle = `hsl(${this.hue}, 100%, 65%)`;
            ctx.strokeStyle = `hsl(${this.hue}, 100%, 55%)`;
            ctx.lineWidth = 0.5;
            
            for (let i = 0; i < 5; i++) {
                ctx.save();
                ctx.rotate((i * Math.PI * 2) / 5);
                ctx.beginPath();
                ctx.ellipse(0, -size * 0.25, size * 0.35, size * 0.5, 0, 0, Math.PI * 2);
                ctx.fill();
                ctx.stroke();
                ctx.restore();
            }
            
            // Nhụy hoa nhỏ hơn
            ctx.fillStyle = '#FFA500';
            ctx.beginPath();
            ctx.arc(0, 0, size * 0.12, 0, Math.PI * 2);
            ctx.fill();
        }

        drawDaoFlower() {
            const ctx = this.ctx;
            const size = this.size;
            
            // Vẽ cánh hoa đào (màu hồng) - nhỏ và mỏng hơn
            ctx.fillStyle = `hsl(${this.hue}, 80%, 75%)`;
            ctx.strokeStyle = `hsl(${this.hue}, 80%, 65%)`;
            ctx.lineWidth = 0.5;
            
            for (let i = 0; i < 5; i++) {
                ctx.save();
                ctx.rotate((i * Math.PI * 2) / 5);
                ctx.beginPath();
                ctx.ellipse(0, -size * 0.25, size * 0.35, size * 0.5, 0, 0, Math.PI * 2);
                ctx.fill();
                ctx.stroke();
                ctx.restore();
            }
            
            // Nhụy hoa nhỏ hơn
            ctx.fillStyle = '#FF1493';
            ctx.beginPath();
            ctx.arc(0, 0, size * 0.12, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    // Animation loop
    function animate() {
        if (!isRunning) return;
        
        const canvas = document.getElementById('tet-flowers-canvas');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Cập nhật và vẽ hoa
        flowers = flowers.filter(flower => {
            if (flower.update()) {
                flower.draw();
                return true;
            }
            return false;
        });
        
        animationId = requestAnimationFrame(animate);
    }

    // Tạo hoa mới
    function spawnFlower() {
        if (!isRunning) return;
        
        const canvas = document.getElementById('tet-flowers-canvas');
        if (!canvas) return;
        
        if (flowers.length < config.maxFlowers) {
            flowers.push(new Flower(canvas));
        }
        
        setTimeout(spawnFlower, config.spawnRate + Math.random() * 500);
    }

    // Resize canvas
    function resizeCanvas() {
        const canvas = document.getElementById('tet-flowers-canvas');
        if (canvas) {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
    }

    // Khởi tạo
    function initTetFlowers(options = {}) {
        // Lưu imagePaths riêng nếu có
        const imagePaths = options.imagePaths;
        
        // Merge options với config (trừ imagePaths)
        const { imagePaths: _, ...restOptions } = options;
        Object.assign(config, restOptions);
        
        // Merge imagePaths riêng để không ghi đè toàn bộ
        if (imagePaths) {
            Object.assign(config.imagePaths, imagePaths);
        }
        
        // Dừng nếu đang chạy
        if (isRunning) {
            stopTetFlowers();
        }
        
        // Tạo canvas
        let canvas = document.getElementById('tet-flowers-canvas');
        if (!canvas) {
            canvas = createCanvas();
        }
        
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);
        
        // Tạo overlay mờ và hình hoa góc
        createOverlay();
        createCornerFlowers();
        
        // Bắt đầu animation
        isRunning = true;
        flowers = [];
        animate();
        spawnFlower();
    }

    // Dừng hiệu ứng
    function stopTetFlowers() {
        isRunning = false;
        if (animationId) {
            cancelAnimationFrame(animationId);
            animationId = null;
        }
        flowers = [];
        
        const canvas = document.getElementById('tet-flowers-canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    }

    // Xóa canvas
    function removeTetFlowers() {
        stopTetFlowers();
        const canvas = document.getElementById('tet-flowers-canvas');
        if (canvas) {
            canvas.remove();
        }
        removeCornerFlowers();
        window.removeEventListener('resize', resizeCanvas);
    }

    // Export functions
    window.initTetFlowers = initTetFlowers;
    window.stopTetFlowers = stopTetFlowers;
    window.removeTetFlowers = removeTetFlowers;

})();

