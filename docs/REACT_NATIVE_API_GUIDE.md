# Guía de Consumo de API para React Native - Sistema de Mantenimientos

## 🚀 Configuración Inicial

### **Base URL y Autenticación**
```javascript
const API_BASE_URL = 'https://tu-api.com/api';
const AUTH_TOKEN = 'tu_jwt_token_aqui';

// Headers por defecto
const defaultHeaders = {
  'Authorization': `Bearer ${AUTH_TOKEN}`,
  'Content-Type': 'application/json',
};
```

## 📱 Endpoints Disponibles

### 1. **Obtener Técnicos Disponibles**

#### **Endpoint:**
```
GET /api/availableTechnicians
```

#### **Parámetros Opcionales:**
- `date` (string): Fecha en formato YYYY-MM-DD
- `shift` (string): Turno 'AM' o 'PM'

#### **Implementación en React Native:**
```javascript
// Servicio
class MaintenanceService {
  async getAvailableTechnicians(date = null, shift = null) {
    const params = new URLSearchParams();
    if (date) params.append('date', date);
    if (shift) params.append('shift', shift);
    
    const queryString = params.toString();
    const url = queryString ? `/availableTechnicians?${queryString}` : '/availableTechnicians';
    
    const response = await fetch(`${API_BASE_URL}${url}`, {
      method: 'GET',
      headers: defaultHeaders,
    });
    
    return response.json();
  }
}

// Uso en componente
const loadTechnicians = async (selectedDate, selectedShift) => {
  try {
    const result = await MaintenanceService.getAvailableTechnicians(selectedDate, selectedShift);
    if (result.success) {
      setTechnicians(result.data);
    }
  } catch (error) {
    console.error('Error cargando técnicos:', error);
  }
};
```

#### **Respuesta:**
```json
{
  "success": true,
  "message": "Available technicians retrieved successfully",
  "data": [
    {
      "id": 27,
      "user_id": 55,
      "document": "1505848568",
      "phone": "3002653698",
      "address": "Prueba",
      "status": "active",
      "user": {
        "id": 55,
        "name": "Cristian vargas",
        "email": "joinernix2@gmail.comj",
        "role": "tecnico"
      }
    }
  ]
}
```

---

### 2. **Obtener Mantenimientos No Asignados**

#### **Endpoint:**
```
GET /api/maintenancesNotAssigned
```

#### **Implementación:**
```javascript
async getUnassignedMaintenances() {
  const response = await fetch(`${API_BASE_URL}/maintenancesNotAssigned`, {
    method: 'GET',
    headers: defaultHeaders,
  });
  
  return response.json();
}
```

#### **Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 62,
      "type": "preventive",
      "status": "pending",
      "client": {
        "id": 1,
        "name": "Empresa XYZ",
        "email": "contacto@empresaxyz.com",
        "phone": "+57 300 123 4567",
        "client_type": "empresa"
      },
      "device": {
        "id": 2,
        "model": "ESP32-CAM",
        "brand": "Espressif",
        "type": "lavadora"
      }
    }
  ]
}
```

---

### 3. **Asignar Mantenimiento**

#### **Endpoint:**
```
PUT /api/assignMaintenance/{maintenance_id}
```

#### **Body Requerido:**
```json
{
  "date_maintenance": "2024-01-15",
  "shift": "AM",
  "technician_id": 1,
  "value": 50000,
  "spare_parts": ["filtro", "aceite", "bujías"]
}
```

#### **Implementación:**
```javascript
async assignMaintenance(maintenanceId, assignmentData) {
  const response = await fetch(`${API_BASE_URL}/assignMaintenance/${maintenanceId}`, {
    method: 'PUT',
    headers: defaultHeaders,
    body: JSON.stringify(assignmentData),
  });
  
  return response.json();
}

// Uso
const handleAssignMaintenance = async (maintenanceId, formData) => {
  try {
    const result = await MaintenanceService.assignMaintenance(maintenanceId, {
      date_maintenance: formData.date,
      shift: formData.shift,
      technician_id: formData.technicianId,
      value: formData.value,
      spare_parts: formData.spareParts,
    });
    
    if (result.success) {
      Alert.alert('Éxito', 'Mantenimiento asignado correctamente');
      // Recargar lista
    } else {
      Alert.alert('Error', result.message);
    }
  } catch (error) {
    Alert.alert('Error', 'Error de conexión');
  }
};
```

#### **Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Maintenance assigned Successfully",
  "data": {
    "id": 62,
    "status": "assigned",
    "technician_id": 1,
    "date_maintenance": "2024-01-15",
    "shift": "AM"
  }
}
```

#### **Respuesta de Error:**
```json
{
  "success": false,
  "message": "Ha ocurrido un error de validación",
  "data": {
    "technician_id": ["El técnico ya tiene un mantenimiento asignado para esta fecha y turno."]
  }
}
```

---

## 🎨 Componentes de UI Completos

### **1. Lista de Mantenimientos Pendientes**
```javascript
import React, { useState, useEffect } from 'react';
import { View, Text, FlatList, TouchableOpacity, StyleSheet } from 'react-native';

const MaintenanceList = ({ onSelectMaintenance }) => {
  const [maintenances, setMaintenances] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadMaintenances();
  }, []);

  const loadMaintenances = async () => {
    setLoading(true);
    try {
      const result = await MaintenanceService.getUnassignedMaintenances();
      if (result.success) {
        setMaintenances(result.data);
      }
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  const renderMaintenance = ({ item }) => (
    <TouchableOpacity 
      style={styles.maintenanceItem}
      onPress={() => onSelectMaintenance(item)}
    >
      <Text style={styles.title}>Mantenimiento #{item.id}</Text>
      <Text style={styles.client}>Cliente: {item.client?.name}</Text>
      <Text style={styles.device}>Dispositivo: {item.device?.model}</Text>
      <Text style={styles.type}>Tipo: {item.type}</Text>
    </TouchableOpacity>
  );

  return (
    <FlatList
      data={maintenances}
      renderItem={renderMaintenance}
      keyExtractor={(item) => item.id.toString()}
      refreshing={loading}
      onRefresh={loadMaintenances}
    />
  );
};

const styles = StyleSheet.create({
  maintenanceItem: {
    backgroundColor: '#fff',
    padding: 15,
    marginBottom: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
  },
  client: {
    fontSize: 14,
    color: '#666',
    marginTop: 5,
  },
  device: {
    fontSize: 14,
    color: '#666',
  },
  type: {
    fontSize: 14,
    color: '#007bff',
    fontWeight: '600',
  },
});

export default MaintenanceList;
```

### **2. Formulario de Asignación**
```javascript
import React, { useState, useEffect } from 'react';
import { View, Text, TextInput, TouchableOpacity, Alert, ScrollView, StyleSheet } from 'react-native';
import { Picker } from '@react-native-picker/picker';

const AssignmentForm = ({ maintenance, onSuccess }) => {
  const [formData, setFormData] = useState({
    date: '',
    shift: 'AM',
    value: '',
    spareParts: [''],
    technicianId: null,
  });
  const [technicians, setTechnicians] = useState([]);

  // Cargar técnicos cuando cambie fecha o turno
  useEffect(() => {
    if (formData.date && formData.shift) {
      loadTechnicians();
    }
  }, [formData.date, formData.shift]);

  const loadTechnicians = async () => {
    try {
      const result = await MaintenanceService.getAvailableTechnicians(
        formData.date, 
        formData.shift
      );
      if (result.success) {
        setTechnicians(result.data);
      }
    } catch (error) {
      console.error('Error cargando técnicos:', error);
    }
  };

  const handleSubmit = async () => {
    try {
      if (!formData.date || !formData.technicianId) {
        Alert.alert('Error', 'Complete todos los campos requeridos');
        return;
      }

      const result = await MaintenanceService.assignMaintenance(maintenance.id, {
        date_maintenance: formData.date,
        shift: formData.shift,
        technician_id: formData.technicianId,
        value: formData.value ? parseInt(formData.value) : null,
        spare_parts: formData.spareParts.filter(part => part.trim() !== ''),
      });
      
      if (result.success) {
        Alert.alert('Éxito', 'Mantenimiento asignado correctamente');
        onSuccess && onSuccess(result);
      } else {
        Alert.alert('Error', result.message);
      }
    } catch (error) {
      Alert.alert('Error', 'Error de conexión');
    }
  };

  return (
    <ScrollView style={styles.container}>
      <Text style={styles.title}>Asignar Mantenimiento</Text>
      
      {/* Fecha */}
      <View style={styles.inputGroup}>
        <Text style={styles.label}>Fecha *</Text>
        <TextInput
          style={styles.input}
          value={formData.date}
          onChangeText={(text) => setFormData(prev => ({ ...prev, date: text }))}
          placeholder="YYYY-MM-DD"
          keyboardType="numeric"
        />
      </View>

      {/* Turno */}
      <View style={styles.inputGroup}>
        <Text style={styles.label}>Turno *</Text>
        <Picker
          selectedValue={formData.shift}
          onValueChange={(value) => setFormData(prev => ({ ...prev, shift: value }))}
          style={styles.picker}
        >
          <Picker.Item label="Mañana (AM)" value="AM" />
          <Picker.Item label="Tarde (PM)" value="PM" />
        </Picker>
      </View>

      {/* Técnico */}
      <View style={styles.inputGroup}>
        <Text style={styles.label}>Técnico *</Text>
        <Picker
          selectedValue={formData.technicianId}
          onValueChange={(value) => setFormData(prev => ({ ...prev, technicianId: value }))}
          style={styles.picker}
        >
          <Picker.Item label="Seleccionar técnico..." value={null} />
          {technicians.map(tech => (
            <Picker.Item 
              key={tech.id} 
              label={`${tech.user.name} - ${tech.user.email}`} 
              value={tech.id} 
            />
          ))}
        </Picker>
      </View>

      {/* Valor */}
      <View style={styles.inputGroup}>
        <Text style={styles.label}>Valor</Text>
        <TextInput
          style={styles.input}
          value={formData.value}
          onChangeText={(text) => setFormData(prev => ({ ...prev, value: text }))}
          placeholder="Valor del mantenimiento"
          keyboardType="numeric"
        />
      </View>

      {/* Repuestos */}
      <View style={styles.inputGroup}>
        <Text style={styles.label}>Repuestos *</Text>
        {formData.spareParts.map((part, index) => (
          <TextInput
            key={index}
            style={styles.input}
            value={part}
            onChangeText={(text) => {
              const newParts = [...formData.spareParts];
              newParts[index] = text;
              setFormData(prev => ({ ...prev, spareParts: newParts }));
            }}
            placeholder={`Repuesto ${index + 1}`}
          />
        ))}
        <TouchableOpacity 
          onPress={() => setFormData(prev => ({ 
            ...prev, 
            spareParts: [...prev.spareParts, ''] 
          }))} 
          style={styles.addButton}
        >
          <Text style={styles.addButtonText}>+ Agregar Repuesto</Text>
        </TouchableOpacity>
      </View>

      <TouchableOpacity onPress={handleSubmit} style={styles.submitButton}>
        <Text style={styles.submitButtonText}>Asignar Mantenimiento</Text>
      </TouchableOpacity>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    backgroundColor: '#f5f5f5',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 20,
    textAlign: 'center',
  },
  inputGroup: {
    marginBottom: 15,
  },
  label: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 5,
    color: '#333',
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    backgroundColor: '#fff',
  },
  picker: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    backgroundColor: '#fff',
  },
  addButton: {
    backgroundColor: '#007bff',
    padding: 10,
    borderRadius: 8,
    marginTop: 10,
  },
  addButtonText: {
    color: '#fff',
    textAlign: 'center',
    fontWeight: '600',
  },
  submitButton: {
    backgroundColor: '#28a745',
    padding: 15,
    borderRadius: 8,
    marginTop: 20,
  },
  submitButtonText: {
    color: '#fff',
    textAlign: 'center',
    fontSize: 18,
    fontWeight: 'bold',
  },
});

export default AssignmentForm;
```

### **3. Pantalla Principal**
```javascript
import React, { useState } from 'react';
import { View, StyleSheet, Modal } from 'react-native';
import MaintenanceList from './MaintenanceList';
import AssignmentForm from './AssignmentForm';

const AssignmentScreen = () => {
  const [selectedMaintenance, setSelectedMaintenance] = useState(null);
  const [showForm, setShowForm] = useState(false);

  const handleSelectMaintenance = (maintenance) => {
    setSelectedMaintenance(maintenance);
    setShowForm(true);
  };

  const handleAssignmentSuccess = () => {
    setShowForm(false);
    setSelectedMaintenance(null);
  };

  return (
    <View style={styles.container}>
      <MaintenanceList onSelectMaintenance={handleSelectMaintenance} />
      
      <Modal
        visible={showForm}
        animationType="slide"
        presentationStyle="pageSheet"
      >
        <AssignmentForm
          maintenance={selectedMaintenance}
          onSuccess={handleAssignmentSuccess}
        />
      </Modal>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
});

export default AssignmentScreen;
```

---

## 🔧 Manejo de Errores

### **Errores Comunes y Soluciones:**

```javascript
const handleAPIError = (error) => {
  if (error.data) {
    // Error de validación con detalles
    const firstError = Object.keys(error.data)[0];
    const errorMessage = error.data[firstError][0];
    Alert.alert('Error de Validación', errorMessage);
  } else if (error.message) {
    // Error general
    Alert.alert('Error', error.message);
  } else {
    // Error de conexión
    Alert.alert('Error', 'Error de conexión con el servidor');
  }
};
```

### **Códigos de Error Específicos:**

| Error | Mensaje | Solución |
|-------|---------|----------|
| `technician_id` | "El técnico ya tiene un mantenimiento asignado para esta fecha y turno" | Seleccionar otro técnico o cambiar fecha/turno |
| `maintenance_id` | "Solo se pueden asignar mantenimientos con estado pendiente" | El mantenimiento ya fue asignado |
| `date_maintenance` | "Formato de fecha inválido" | Usar formato YYYY-MM-DD |
| `shift` | "Turno debe ser AM o PM" | Seleccionar AM o PM |

---

## 📦 Dependencias Requeridas

```json
{
  "dependencies": {
    "@react-native-async-storage/async-storage": "^1.19.0",
    "@react-native-picker/picker": "^2.4.0",
    "react-native": "^0.72.0"
  }
}
```

## 🚀 Instalación

```bash
npm install @react-native-async-storage/async-storage @react-native-picker/picker
cd ios && pod install
```

---

## ✅ Listo para Usar

Con esta documentación tienes todo lo necesario para implementar el sistema de asignación de mantenimientos en React Native. Los componentes están listos para usar y manejan todos los casos de error y validación.
