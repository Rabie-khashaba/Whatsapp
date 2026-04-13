// ============================================
// Baileys WhatsApp Bot - Latest Version Compatible
// ============================================

const { 
    default: makeWASocket, 
    useMultiFileAuthState, 
    DisconnectReason,
    Browsers
} = require('@whiskeysockets/baileys');
const express = require('express');
const pino = require('pino');
const fs = require('fs');

// ============================================
// Express Server Setup
// ============================================
const app = express();
app.use(express.json());

// Map للـ instances
const instances = new Map();

// ============================================
// Connect to WhatsApp Function
// ============================================
async function connectToWhatsApp(instanceId) {
    console.log(`[${instanceId}] Starting connection...`);
    
    // مجلد حفظ الـ Session
    const authFolder = `./auth_${instanceId}`;
    if (!fs.existsSync(authFolder)) {
        fs.mkdirSync(authFolder, { recursive: true });
    }
    
    // تحميل/إنشاء Session
    const { state, saveCreds } = await useMultiFileAuthState(authFolder);
    
    // إنشاء Socket
    const sock = makeWASocket({
        auth: state,
        printQRInTerminal: true, // طباعة QR في Terminal
        logger: pino({ level: 'silent' }), // إيقاف الـ logs الكتيرة
        browser: Browsers.windows('Desktop'),
        defaultQueryTimeoutMs: undefined
    });
    
    // حفظ الـ Credentials عند التحديث
    sock.ev.on('creds.update', saveCreds);
    
    // معالجة الـ Connection Updates
    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;
        
        // عرض QR Code
        if (qr) {
            console.log(`\n[${instanceId}] 📱 QR Code Generated!`);
            console.log('Scan this with WhatsApp on your phone');
            console.log('Settings → Linked Devices → Link a Device\n');
        }
        
        // إعادة الاتصال عند القطع
        if (connection === 'close') {
            const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
            
            console.log(`[${instanceId}] Connection closed. Reconnect: ${shouldReconnect}`);
            
            if (shouldReconnect) {
                console.log(`[${instanceId}] Reconnecting in 5 seconds...`);
                setTimeout(() => connectToWhatsApp(instanceId), 5000);
            } else {
                instances.delete(instanceId);
                console.log(`[${instanceId}] ❌ Logged out permanently`);
            }
        } else if (connection === 'open') {
            console.log(`[${instanceId}] ✅ Connected successfully!`);
            console.log(`[${instanceId}] Phone: ${sock.user?.id || 'Unknown'}`);
        }
    });
    
    // استقبال الرسائل (اختياري)
    sock.ev.on('messages.upsert', async ({ messages, type }) => {
        if (type !== 'notify') return;
        
        const msg = messages[0];
        if (!msg.message) return;
        
        const from = msg.key.remoteJid;
        const text = msg.message.conversation || 
                    msg.message.extendedTextMessage?.text || '';
        
        console.log(`[${instanceId}] 📩 Message from ${from}: ${text}`);
        
        // مثال: رد تلقائي
        if (text.toLowerCase() === 'ping') {
            await sock.sendMessage(from, { text: 'Pong! 🏓' });
        }
    });
    
    // حفظ الـ Instance
    instances.set(instanceId, sock);
    
    return sock;
}

// ============================================
// API Endpoints
// ============================================

// 1. Home / Health Check
app.get('/', (req, res) => {
    const instancesList = Array.from(instances.entries()).map(([id, sock]) => ({
        id,
        connected: sock.user ? true : false,
        phone: sock.user?.id || null
    }));

    res.json({
        status: 'running',
        message: 'Baileys WhatsApp Bot',
        instances: instancesList,
        total: instances.size,
        timestamp: new Date().toISOString()
    });
});

// 2. إنشاء Instance جديد
app.post('/api/instance/create', async (req, res) => {
    try {
        const { instanceId } = req.body;
        
        if (!instanceId) {
            return res.status(400).json({
                success: false,
                error: 'instanceId is required'
            });
        }
        
        if (instances.has(instanceId)) {
            return res.json({
                success: true,
                message: 'Instance already exists',
                instanceId
            });
        }
        
        // Start connection in background
        connectToWhatsApp(instanceId);
        
        res.json({
            success: true,
            message: 'Instance creation started. Check logs for QR code',
            instanceId
        });
        
    } catch (error) {
        console.error('Create instance error:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// 3. إرسال رسالة
app.post('/api/instance/:instanceId/send', async (req, res) => {
    try {
        const { instanceId } = req.params;
        const { phone, message } = req.body;
        
        console.log(`[${instanceId}] Send request received for ${phone}`);
        
        // التحقق من البيانات
        if (!phone || !message) {
            return res.status(400).json({
                success: false,
                error: 'phone and message are required',
                received: { phone, message }
            });
        }
        
        // جلب الـ Instance
        let instance = instances.get(instanceId);
        
        // إنشاء Instance جديد لو مش موجود
        if (!instance) {
            console.log(`[${instanceId}] Instance not found, creating new one...`);
            instance = await connectToWhatsApp(instanceId);
            
            // انتظار الاتصال
            console.log(`[${instanceId}] Waiting for connection...`);
            await new Promise(resolve => setTimeout(resolve, 3000));
            
            // تحقق من الاتصال
            if (!instance.user) {
                return res.status(503).json({
                    success: false,
                    error: 'Instance not connected. Please scan QR code first',
                    message: 'Check server logs for QR code'
                });
            }
        }
        
        // تنسيق رقم الهاتف
        let formattedPhone = phone.replace(/\D/g, ''); // إزالة كل شيء غير الأرقام
        
        // إضافة @s.whatsapp.net
        if (!formattedPhone.includes('@')) {
            formattedPhone = `${formattedPhone}@s.whatsapp.net`;
        }
        
        console.log(`[${instanceId}] Sending to ${formattedPhone}...`);
        
        // إرسال الرسالة
        const result = await instance.sendMessage(formattedPhone, { 
            text: message 
        });
        
        console.log(`[${instanceId}] ✅ Message sent successfully`);
        
        res.json({
            success: true,
            message: 'Message sent successfully',
            data: {
                phone: formattedPhone,
                messageId: result.key.id,
                timestamp: Date.now()
            }
        });
        
    } catch (error) {
        console.error(`[${req.params.instanceId}] Send error:`, error);
        res.status(500).json({
            success: false,
            error: error.message,
            details: error.toString()
        });
    }
});

// 4. إرسال صورة
app.post('/api/instance/:instanceId/send-image', async (req, res) => {
    try {
        const { instanceId } = req.params;
        const { phone, imageUrl, caption } = req.body;
        
        let instance = instances.get(instanceId);
        if (!instance || !instance.user) {
            return res.status(404).json({
                success: false,
                error: 'Instance not connected'
            });
        }
        
        let formattedPhone = phone.replace(/\D/g, '');
        if (!formattedPhone.includes('@')) {
            formattedPhone = `${formattedPhone}@s.whatsapp.net`;
        }
        
        await instance.sendMessage(formattedPhone, {
            image: { url: imageUrl },
            caption: caption || ''
        });
        
        res.json({
            success: true,
            message: 'Image sent successfully'
        });
        
    } catch (error) {
        console.error('Send image error:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// 5. حالة Instance
app.get('/api/instance/:instanceId/status', (req, res) => {
    const { instanceId } = req.params;
    const instance = instances.get(instanceId);
    
    if (!instance) {
        return res.status(404).json({
            success: false,
            status: 'not_found',
            message: 'Instance does not exist'
        });
    }
    
    const connected = instance.user ? true : false;
    
    res.json({
        success: true,
        status: connected ? 'connected' : 'disconnected',
        instanceId,
        phone: instance.user?.id || null,
        name: instance.user?.name || null
    });
});

// 6. Logout
app.post('/api/instance/:instanceId/logout', async (req, res) => {
    try {
        const { instanceId } = req.params;
        const instance = instances.get(instanceId);
        
        if (!instance) {
            return res.status(404).json({
                success: false,
                error: 'Instance not found'
            });
        }
        
        await instance.logout();
        instances.delete(instanceId);
        
        // حذف Session
        const authFolder = `./auth_${instanceId}`;
        if (fs.existsSync(authFolder)) {
            fs.rmSync(authFolder, { recursive: true, force: true });
        }
        
        res.json({
            success: true,
            message: 'Logged out successfully'
        });
        
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// 7. قائمة كل الـ Instances
app.get('/api/instances', (req, res) => {
    const instancesList = Array.from(instances.entries()).map(([id, sock]) => ({
        instanceId: id,
        connected: sock.user ? true : false,
        phone: sock.user?.id || null,
        name: sock.user?.name || null
    }));
    
    res.json({
        success: true,
        instances: instancesList,
        total: instancesList.length
    });
});

// ============================================
// Error Handling
// ============================================
app.use((err, req, res, next) => {
    console.error('Server Error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal server error',
        message: err.message
    });
});

// ============================================
// Start Server
// ============================================
const PORT = process.env.PORT || 3000;

app.listen(PORT, async () => {
    console.log('\n=================================');
    console.log('🚀 Baileys WhatsApp Bot Started');
    console.log(`📡 Server: http://localhost:${PORT}`);
    console.log('=================================\n');
    
    // Auto-connect instance1 عند البدء
    console.log('Starting instance1...\n');
    try {
        await connectToWhatsApp('instance1');
    } catch (error) {
        console.error('Auto-connect failed:', error.message);
    }
});

// ============================================
// Graceful Shutdown
// ============================================
process.on('SIGINT', async () => {
    console.log('\n⏳ Shutting down gracefully...');
    
    // إغلاق كل الـ Connections
    for (const [id, sock] of instances.entries()) {
        console.log(`Closing instance: ${id}`);
        try {
            await sock.end();
        } catch (error) {
            console.error(`Error closing ${id}:`, error.message);
        }
    }
    
    console.log('✅ Shutdown complete');
    process.exit(0);
});