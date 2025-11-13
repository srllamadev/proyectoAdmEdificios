# 🤖 Chatbot con IA - Edificio AI

Sistema de chatbot inteligente integrado con DeepSeek AI para el panel de administración.

## 🚀 Características

- **IA Conversacional**: Usa DeepSeek AI para respuestas inteligentes
- **Contexto en Tiempo Real**: Accede a datos actualizados del edificio
- **Interfaz Moderna**: Widget flotante estilo chat profesional
- **Respuestas Personalizadas**: Conoce el estado completo del edificio
- **Sugerencias Rápidas**: Atajos para preguntas comunes

## 📊 Información que Maneja

El chatbot tiene acceso a:
- 📈 Estadísticas de departamentos e inquilinos
- 💰 Estado de pagos y deudas
- ⚡ Consumos de servicios (agua, luz, gas)
- 📅 Reservas y áreas comunes
- 🔒 Eventos de seguridad
- 🏆 Rankings y análisis

## ⚙️ Configuración

### 1. Archivo de Variables de Entorno

Copia `.env.example` a `.env`:

```bash
cp .env.example .env
```

### 2. Configura tu API Key

Edita el archivo `.env` y agrega tu API Key de DeepSeek:

```env
DEEPSEEK_API_KEY=tu_api_key_aqui
```

### 3. Obtener API Key de DeepSeek

1. Visita: https://platform.deepseek.com/
2. Crea una cuenta o inicia sesión
3. Ve a "API Keys" en el dashboard
4. Crea una nueva API Key
5. Copia la clave y pégala en tu archivo `.env`

## 🔒 Seguridad

**IMPORTANTE**: 
- ✅ El archivo `.env` está en `.gitignore` y NO se subirá al repositorio
- ✅ Nunca compartas tu API Key públicamente
- ✅ Usa `.env.example` como plantilla (sin claves reales)
- ✅ El chatbot solo está disponible para administradores autenticados

## 📁 Estructura de Archivos

```
proyectoAdmEdificios/
├── .env                          # Variables de entorno (NO subir a git)
├── .env.example                  # Plantilla de variables
├── includes/
│   ├── env_loader.php            # Cargador de variables .env
│   └── deepseek_client.php       # Cliente API de DeepSeek
├── api/
│   └── chatbot.php               # Endpoint del chatbot
└── views/admin/
    └── dashboard.php             # Dashboard con widget del chatbot
```

## 💬 Uso

1. **Accede al dashboard de administrador**
2. **Haz clic en el botón flotante** del robot en la esquina inferior derecha
3. **Escribe tu pregunta** o usa las sugerencias rápidas
4. **Recibe respuestas inteligentes** basadas en datos reales

### Ejemplos de Preguntas

- "¿Cuánto se debe en total?"
- "¿Cuál es el consumo de luz del mes?"
- "¿Cuántos pagos están vencidos?"
- "Dame un resumen general del edificio"
- "¿Qué departamentos deben más?"
- "¿Cuántas reservas hay pendientes?"

## 🛠️ API Endpoints

### POST `/api/chatbot.php`

**Acción: send_message**

```json
{
  "action": "send_message",
  "message": "¿Cuánto se debe?",
  "history": []
}
```

**Respuesta:**

```json
{
  "success": true,
  "response": "El total de deuda es...",
  "usage": {
    "prompt_tokens": 150,
    "completion_tokens": 75,
    "total_tokens": 225
  }
}
```

**Acción: get_building_stats**

```json
{
  "action": "get_building_stats"
}
```

## 🎨 Personalización

### Modificar el Prompt del Sistema

Edita `api/chatbot.php` en la función `createSystemPrompt()` para cambiar el comportamiento del chatbot.

### Agregar Más Contexto

Modifica `api/chatbot.php` en la función `getBuildingContext()` para incluir más datos del edificio.

### Cambiar Estilos

Los estilos del widget están en `views/admin/dashboard.php` dentro del bloque `<style>`.

## 📊 Límites y Costos

DeepSeek tiene planes gratuitos y de pago:
- **Free Tier**: Incluye créditos gratuitos
- **Pay-as-you-go**: Paga por uso después de los créditos gratuitos

Consulta la documentación oficial: https://platform.deepseek.com/docs

## 🐛 Solución de Problemas

### Error: "API Key de DeepSeek no configurada"

- Verifica que el archivo `.env` existe
- Verifica que `DEEPSEEK_API_KEY` está configurada correctamente
- Reinicia el servidor web (XAMPP)

### Error: "Error de conexión"

- Verifica tu conexión a internet
- Verifica que la API Key es válida
- Revisa el log de errores de PHP

### El chatbot no responde

- Abre la consola del navegador (F12) para ver errores JavaScript
- Verifica que estás autenticado como administrador
- Revisa los logs del servidor

## 📝 Notas Importantes

1. El chatbot **solo está disponible en el dashboard de administrador**
2. Todas las consultas requieren **autenticación**
3. El historial de conversación se mantiene **solo en la sesión actual**
4. El chatbot usa **datos en tiempo real** de la base de datos

## 🔄 Actualizaciones Futuras

- [ ] Historial de conversaciones persistente
- [ ] Exportar conversaciones
- [ ] Múltiples idiomas
- [ ] Comandos especiales (ej: /estadisticas)
- [ ] Integración con notificaciones
- [ ] Modo de voz

## 📞 Soporte

Para problemas o preguntas sobre el chatbot, contacta al equipo de desarrollo.

---

**Desarrollado con ❤️ para el Sistema de Administración de Edificios**
