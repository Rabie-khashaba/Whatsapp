// server.js - Enhanced Baileys API Server with Multi-Instance Support
const express = require('express');
const { default: makeWASocket, useMultiFileAuthState, DisconnectReason } = require('@whiskeysockets/baileys');
const QRCode = require('qrcode');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(express.json());

// ✅ تخزين معزول لكل instance
const instances = new Map();

// مجلد Auth States
const AUTH_DIR = path.join(__dirname, 'auth_states');
if (!fs.existsSync(AUTH_DIR)) {
    fs.mkdirSync(AUTH_DIR);
}

// ========================================
// Helper Functions
// ========================================

/**
 * إنشاء WhatsApp Socket لـ Instance معين
 */
async function createSocket(instanceId) {
    const authPath = path.join(AUTH_DIR, instanceId);
    
    if (!fs.existsSync(authPath)) {
        fs.mkdirSync(authPath, { recursive: true });
    }

    const { state, saveCreds } = await useMultiFileAuthState(authPath);
    
    const sock = makeWASocket({
        auth: state,
        printQRInTerminal: false,
        defaultQueryTimeoutMs: undefined,
    });

    let qrCode = null;
    let phoneNumber = null;

    // Event: QR Code Generation
    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            qrCode = await QRCode.toDataURL(qr);
            console.log(`[${instanceId}] QR Code generated`);
            
            // تحديث في Map
            const instance = instances.get(instanceId);
            if (instance) {
                instance.qrCode = qrCode;
            }
        }

        if (connection === 'close') {
            const shouldReconnect = 
                lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
            
            console.log(`[${instanceId}] Connection closed. Reconnect: ${shouldReconnect}`);
            
            if (shouldReconnect) {
                // إعادة الاتصال تلقائياً
                setTimeout(async () => {
                    console.log(`[${instanceId}] Attempting to reconnect...`);
                    try {
                        const newSock = await createSocket(instanceId);
                        instances.set(instanceId, {
                            sock: newSock,
                            qrCode: null,
                            status: 'pending',
                            phoneNumber: null,
                            createdAt: Date.now()
                        });
                    } catch (error) {
                        console.error(`[${instanceId}] Reconnection failed:`, error);
                    }
                }, 3000);
            } else {
                // Logged out - حذف Instance
                instances.delete(instanceId);
                console.log(`[${instanceId}] Instance removed (logged out)`);
            }
        } else if (connection === 'open') {
            phoneNumber = sock.user?.id?.split(':')[0] || null;
            console.log(`[${instanceId}] Connected! Phone: ${phoneNumber}`);
            
            // تحديث في Map
            const instance = instances.get(instanceId);
            if (instance) {
                instance.status = 'connected';
                instance.phoneNumber = phoneNumber;
                instance.qrCode = null; // مش محتاجين QR بعد الاتصال
            }
        }
    });

    // Event: Credentials Update
    sock.ev.on('creds.update', saveCreds);

    // Event: Messages (اختياري - للتطوير المستقبلي)
    sock.ev.on('messages.upsert', async ({ messages }) => {
        for (const msg of messages) {
            console.log(`[${instanceId}] New message:`, msg.key.id);
            // يمكنك إضافة webhook هنا لإرسال الرسائل لـ Laravel
        }
    });

    return sock;
}

// ========================================
// API Endpoints
// ========================================

/**
 * ✅ إنشاء Instance جديد
 */
app.post('/api/instance/create', async (req, res) => {
    try {
        const { instanceId } = req.body;
        
        if (!instanceId) {
            return res.status(400).json({ error: 'instanceId required' });
        }

        // فحص إذا Instance موجود بالفعل
        if (instances.has(instanceId)) {
            return res.status(409).json({ 
                error: 'Instance already exists',
                instanceId 
            });
        }

        console.log(`[${instanceId}] Creating new instance...`);

        const sock = await createSocket(instanceId);

        instances.set(instanceId, {
            sock,
            qrCode: null,
            status: 'pending',
            phoneNumber: null,
            createdAt: Date.now()
        });

        res.json({
            success: true,
            instanceId,
            message: 'Instance created successfully',
            status: 'pending'
        });

    } catch (error) {
        console.error('Error creating instance:', error);
        res.status(500).json({ error: error.message });
    }
});

/**
 * ✅ الحصول على QR Code
 */
app.get('/api/instance/:instanceId/qr', async (req, res) => {
    try {
        const { instanceId } = req.params;
        const instance = instances.get(instanceId);

        if (!instance) {
            return res.status(404).json({ error: 'Instance not found' });
        }

        if (instance.status === 'connected') {
            return res.status(400).json({ 
                error: 'Instance already connected',
                phoneNumber: instance.phoneNumber
            });
        }

        if (!instance.qrCode) {
            return res.status(404).json({ 
                error: 'QR Code not available yet',
                message: 'Wait a few seconds and try again'
            });
        }

        res.json({
            success: true,
            qrCode: instance.qrCode.replace('data:image/png;base64,', ''),
            instanceId
        });

    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

/**
 * ✅ فحص حالة Instance
 */
app.get('/api/instance/:instanceId/status', (req, res) => {
    try {
        const { instanceId } = req.params;
        const instance = instances.get(instanceId);

        if (!instance) {
            return res.status(404).json({ error: 'Instance not found' });
        }

        const isConnected = instance.sock.user ? true : false;

        res.json({
            success: true,
            instanceId,
            status: isConnected ? 'connected' : 'pending',
            phoneNumber: instance.phoneNumber,
            uptime: Date.now() - instance.createdAt
        });

    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

/**
 * ✅ قائمة جميع الـ Instances
 */
app.get('/api/instances/list', (req, res) => {
    try {
        const list = [];
        
        instances.forEach((instance, instanceId) => {
            list.push({
                instanceId,
                status: instance.status,
                phoneNumber: instance.phoneNumber,
                uptime: Date.now() - instance.createdAt,
                hasQR: instance.qrCode ? true : false
            });
        });

        res.json({
            success: true,
            count: list.length,
            instances: list
        });

    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

/**
 * ✅ إرسال رسالة
 */
app.post('/api/instance/:instanceId/send', async (req, res) => {
    try {
        const { instanceId } = req.params;
        const { phone, message, mediaUrl } = req.body;

        const instance = instances.get(instanceId);

        if (!instance) {
            return res.status(404).json({ error: 'Instance not found' });
        }

        if (!instance.sock.user) {
            return res.status(400).json({ error: 'Instance not connected' });
        }

        const jid = phone.includes('@') ? phone : `${phone}@s.whatsapp.net`;

        // إرسال رسالة نصية أو صورة
        if (mediaUrl) {
            await instance.sock.sendMessage(jid, {
                image: { url: mediaUrl },
                caption: message
            });
        } else {
            await instance.sock.sendMessage(jid, { text: message });
        }

        console.log(`[${instanceId}] Message sent to ${phone}`);

        res.json({
            success: true,
            instanceId,
            message: 'Message sent successfully',
            to: phone
        });

    } catch (error) {
        console.error('Send message error:', error);
        res.status(500).json({ error: error.message });
    }
});

/**
 * ✅ Logout من Instance
 */
// app.post('/api/instance/:instanceId/logout', async (req, res) => {
//     try {
//         const { instanceId } = req.params;
//         const instance = instances.get(instanceId);

//         if (!instance) {
//             return res.status(404).json({ error: 'Instance not found' });
//         }

//         console.log(`[${instanceId}] Logging out...`);

//         await instance.sock.logout();
//         //instances.delete(instanceId);


//         instance.status = 'pending';
//     instance.qrCode = null;

//     // 🔥 أنشئ socket جديد
//     const sock = await createSocket(instanceId);
//     instance.sock = sock;



//         // حذف Auth State
//         const authPath = path.join(AUTH_DIR, instanceId);
//         if (fs.existsSync(authPath)) {
//             fs.rmSync(authPath, { recursive: true });
//         }

//         res.json({
//             success: true,
//             message: 'Logged out successfully',
//             instanceId
//         });

//     } catch (error) {
//         console.error('Logout error:', error);
//         res.status(500).json({ error: error.message });
//     }
// });


app.post('/api/instance/:instanceId/logout', async (req, res) => {
    const { instanceId } = req.params;
    const instance = instances.get(instanceId);

    if (!instance) {
        return res.status(404).json({ error: 'Instance not found' });
    }

    console.log(`[${instanceId}] Logging out...`);

    await instance.sock.logout();
    instances.delete(instanceId);

    // ✅ امسح auth_states
    const authPath = path.join(AUTH_DIR, instanceId);
    if (fs.existsSync(authPath)) {
        fs.rmSync(authPath, { recursive: true });
    }

    res.json({ success: true });
});

/**
 * ✅ حذف Instance
 */
app.delete('/api/instance/:instanceId', async (req, res) => {
    try {
        const { instanceId } = req.params;
        const instance = instances.get(instanceId);

        if (instance) {
            try {
                await instance.sock.logout();
            } catch (error) {
                console.log(`[${instanceId}] Logout failed during delete:`, error.message);
            }
        }

        instances.delete(instanceId);

        // حذف Auth State
        const authPath = path.join(AUTH_DIR, instanceId);
        if (fs.existsSync(authPath)) {
            fs.rmSync(authPath, { recursive: true });
        }

        console.log(`[${instanceId}] Instance deleted`);

        res.json({
            success: true,
            message: 'Instance deleted successfully',
            instanceId
        });

    } catch (error) {
        console.error('Delete error:', error);
        res.status(500).json({ error: error.message });
    }
});

/**
 * ✅ Health Check
 */
app.get('/api/health', (req, res) => {
    res.json({
        success: true,
        message: 'Baileys API is running',
        timestamp: new Date().toISOString(),
        activeInstances: instances.size,
        uptime: process.uptime()
    });
});

// ========================================
// استرجاع Instances عند إعادة تشغيل السيرفر
// ========================================
async function restoreInstances() {
    console.log('🔄 Restoring instances from auth_states...');
    
    if (!fs.existsSync(AUTH_DIR)) {
        console.log('No auth_states directory found');
        return;
    }

    const dirs = fs.readdirSync(AUTH_DIR);
    
    for (const instanceId of dirs) {
        const authPath = path.join(AUTH_DIR, instanceId);
        
        // تخطي الملفات غير المجلدات
        if (!fs.statSync(authPath).isDirectory()) {
            continue;
        }

        try {
            console.log(`[${instanceId}] Restoring...`);
            
            const sock = await createSocket(instanceId);

            instances.set(instanceId, {
                sock,
                qrCode: null,
                status: 'connecting',
                phoneNumber: null,
                createdAt: Date.now()
            });

            console.log(`[${instanceId}] Restored successfully`);
        } catch (error) {
            console.error(`[${instanceId}] Failed to restore:`, error.message);
        }
    }

    console.log(`✅ Restored ${instances.size} instances`);
}

// ========================================
// Server Startup
// ========================================
const PORT = process.env.PORT || 3000;

app.listen(PORT, async () => {
    console.log('========================================');
    console.log(`🚀 Baileys API Server running on port ${PORT}`);
    console.log('========================================');
    
    await restoreInstances();
    
    console.log('');
    console.log('📊 Server Stats:');
    console.log(`   Active Instances: ${instances.size}`);
    console.log(`   Auth Directory: ${AUTH_DIR}`);
    console.log('');
    console.log('✅ Server ready to accept requests');
    console.log('========================================');
});

// ========================================
// Graceful Shutdown
// ========================================
process.on('SIGINT', async () => {
    console.log('');
    console.log('🛑 Shutting down gracefully...');
    
    // إغلاق كل الاتصالات
    for (const [instanceId, instance] of instances) {
        try {
            console.log(`[${instanceId}] Closing connection...`);
            await instance.sock.end();
        } catch (error) {
            console.error(`[${instanceId}] Error closing:`, error.message);
        }
    }
    
    console.log('✅ Shutdown complete');
    process.exit(0);
});