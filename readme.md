# CentralLab – MQTT Broker em PHP para IoT, OTA e Persistência de Dados

Infraestrutura **IoT completa e leve**, desenvolvida para o **CentralLab**, baseada em **MQTT puro (3.1.1)**, utilizando **PHP** no backend e **ESP8266 (ESP-01S)** em campo.  
O projeto elimina a dependência de brokers externos (Mosquitto), oferecendo **controle total do protocolo**.

---

## Visão Geral

Este sistema foi projetado para:

- Coletar dados de sensores (DS18B20)
- Transmitir dados via MQTT
- Persistir leituras em banco de dados MySQL
- Atualizar firmware remotamente (OTA via MQTT + HTTP)
- Operar com dispositivos em **Deep Sleep**

Tudo isso usando **MQTT implementado manualmente**, byte a byte.

---

## Tecnologias Utilizadas

### Backend
- **PHP 8+**
- **Workerman** (event-loop TCP assíncrono)
- **MySQL**
- **MQTT 3.1.1 (RAW / Low-Level)**

### Embarcado
- **ESP8266 (ESP-01S)**
- Deep Sleep
- Watchdog Timer (WDT)
- OTA via HTTP acionado por MQTT

### Comunicação
- TCP Socket puro
- MQTT sem bibliotecas externas
- JSON

---

## broker.php — MQTT Broker em PHP

### Descrição

Broker MQTT funcional escrito **100% em PHP**, utilizando **Workerman** para gerenciamento de conexões TCP.

### Funcionalidades

- Porta **1883**
- Suporte aos pacotes:
  - CONNECT
  - SUBSCRIBE
  - PUBLISH
  - PINGREQ
  - DISCONNECT
- Mensagens **RETAIN**
- Múltiplos clientes por tópico
- Tabelas totalmente **em memória**
- Redistribuição automática de mensagens

### Implementação Técnica

- Decodificação manual do `Remaining Length`
- Leitura de strings MQTT (2 bytes + payload)
- Construção manual de pacotes PUBLISH
- Gerenciamento de assinantes por tópico

---

## DbLogger.php — Persistência MQTT → MySQL

### Descrição

Classe responsável por **interpretar mensagens MQTT** e **persistir leituras de sensores** no banco de dados.

### Tópicos Monitorados

laboratorio/leituras/#

### Payload Esperado

```json
{
  "mac": "5C:CF:7F:F7:F0:32",
  "addr_0": "28FF641D...",
  "temp_0": 4.25,
  "addr_1": "28FF12AB...",
  "temp_1": 4.31
}
```

---

### Estrutura de Inserção

Cada sensor é salvo individualmente:

Campo	Descrição

id_refrigerator	Identificador lógico

mac_esp	MAC do ESP

id_sensor	Endereço DS18B20

temperature	Temperatura

### Segurança & Robustez

Reconexão automática com MySQL

Prepared Statements

Validação de JSON

Ignora sensores inválidos (N/A)

## enviar_ota.php — OTA via MQTT RAW

### Descrição

Script PHP responsável por enviar comandos OTA diretamente via MQTT, sem bibliotecas MQTT.

### Tópico de Comando

laboratorio/comandos/geral

### Payload OTA

```
{
  "mac": "5C:CF:7F:F7:F0:32",
  "acao": "OTA",
  "versao": 5,
  "url": "http://192.168.2.2/centrallab/firmware_v5.bin"
}
```

### Uso de RETAIN

Garante entrega mesmo após Deep Sleep

ESP recebe o comando assim que reconecta

Ideal para dispositivos intermitentes

### Fluxo de Operação

1. ESP acorda do Deep Sleep

2. Conecta ao Wi-Fi

3. Conecta ao broker MQTT

4. Publica leituras dos sensores

5. Backend salva no MySQL

6. ESP verifica comandos OTA

7. Se houver atualização → OTA via HTTP

8. ESP volta ao Deep Sleep

# Autor
### Renan Saraiva dos Santos
Engenharia de Controle e Automação – CentralLab
IoT • Sistemas Embarcados • MQTT • Edge Systems
