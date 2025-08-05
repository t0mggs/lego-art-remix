# 📁 NUEVA ESTRUCTURA PARA GITHUB + SHOPIFY

## 🏗️ ESTRUCTURA DEL REPOSITORIO

```
visubloq-repo/
├── 📁 shopify-theme/          # Lo que va a Shopify
│   ├── assets/
│   │   ├── visubloq.js        # Tu app principal
│   │   ├── visubloq.css
│   │   └── fonts/
│   ├── snippets/
│   │   └── visubloq-app.liquid # Snippet para incluir en cualquier página
│   └── templates/
│       └── page.visubloq.liquid # Página dedicada (opcional)
│
├── 📁 api/                    # Backend que va a Vercel
│   ├── vercel.json           # Configuración de Vercel
│   ├── config.php           # Configuración global
│   ├── webhook.php          # Recibe webhooks de Shopify
│   ├── save-pdf.php         # Guarda PDFs
│   ├── admin.php            # Panel admin
│   └── database/
│       └── setup.sql        # SQL para PlanetScale
│
├── 📁 storage/               # Para desarrollo local
│   └── pdfs/
│
├── README.md
├── .gitignore
└── package.json
```

## 🔄 FLUJO DE TRABAJO

1. **Desarrollo:** Trabajas en tu repo local
2. **Push a GitHub:** Subes cambios
3. **Shopify sync:** Shopify toma los cambios del tema
4. **Vercel deploy:** API se despliega automáticamente
5. **Base de datos:** PlanetScale maneja los datos

## 📋 VENTAJAS DE ESTA ESTRUCTURA

✅ **Todo en un repo** - Fácil de mantener
✅ **Gratis** - Servicios sin costo
✅ **Escalable** - Puede crecer con tu negocio
✅ **Profesional** - Arquitectura estándar de la industria
✅ **Compatible con Shopify** - Sin problemas de integración
