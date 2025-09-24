# Ejemplos de Implementación en React Native

## Configuración Base

### 1. Servicio de API
```javascript
// services/MaintenanceService.js
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'https://tu-api.com/api';

class MaintenanceService {
  async getAuthToken() {
    return await AsyncStorage.getItem('auth_token');
  }

  async makeRequest(endpoint, options = {}) {
    const token = await this.getAuthToken();
    
    const defaultOptions = {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    };

    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      ...defaultOptions,
      ...options,
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Error en la petición');
    }

    return data;
  }

  // Asignar mantenimiento
  async assignMaintenance(maintenanceId, assignmentData) {
    return this.makeRequest(`/assignMaintenance/${maintenanceId}`, {
      method: 'PUT',
      body: JSON.stringify(assignmentData),
    });
  }

  // Obtener mantenimientos no asignados
  async getUnassignedMaintenances() {
    return this.makeRequest('/maintenancesNotAssigned');
  }

  // Obtener técnicos disponibles
  async getAvailableTechnicians(date, shift) {
    return this.makeRequest(`/availableDates?date=${date}&shift=${shift}`);
  }
}

export default new MaintenanceService();
```

### 2. Hook Personalizado
```javascript
// hooks/useMaintenanceAssignment.js
import { useState, useCallback } from 'react';
import MaintenanceService from '../services/MaintenanceService';

export const useMaintenanceAssignment = () => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const assignMaintenance = useCallback(async (maintenanceId, assignmentData) => {
    setLoading(true);
    setError(null);

    try {
      const result = await MaintenanceService.assignMaintenance(maintenanceId, assignmentData);
      return result;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  return {
    assignMaintenance,
    loading,
    error,
  };
};
```

## Componentes de UI

### 1. Formulario de Asignación
```javascript
// components/AssignmentForm.js
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  Alert,
  ScrollView,
  StyleSheet,
} from 'react-native';
import { Picker } from '@react-native-picker/picker';
import { useMaintenanceAssignment } from '../hooks/useMaintenanceAssignment';

const AssignmentForm = ({ maintenance, onSuccess }) => {
  const { assignMaintenance, loading, error } = useMaintenanceAssignment();
  
  const [formData, setFormData] = useState({
    date: '',
    shift: 'AM',
    value: '',
    spareParts: [''],
    technicianId: null,
  });

  const [technicians, setTechnicians] = useState([]);

  // Cargar técnicos disponibles cuando cambie la fecha o turno
  useEffect(() => {
    if (formData.date && formData.shift) {
      loadAvailableTechnicians();
    }
  }, [formData.date, formData.shift]);

  const loadAvailableTechnicians = async () => {
    try {
      const result = await MaintenanceService.getAvailableTechnicians(
        formData.date, 
        formData.shift
      );
      setTechnicians(result.data || []);
    } catch (err) {
      console.error('Error cargando técnicos:', err);
    }
  };

  const handleSubmit = async () => {
    try {
      // Validación básica
      if (!formData.date || !formData.technicianId) {
        Alert.alert('Error', 'Por favor complete todos los campos requeridos');
        return;
      }

      const assignmentData = {
        date_maintenance: formData.date,
        shift: formData.shift,
        value: formData.value ? parseInt(formData.value) : null,
        spare_parts: formData.spareParts.filter(part => part.trim() !== ''),
        technician_id: formData.technicianId,
      };

      const result = await assignMaintenance(maintenance.id, assignmentData);
      
      Alert.alert('Éxito', 'Mantenimiento asignado correctamente');
      onSuccess && onSuccess(result);
      
    } catch (err) {
      Alert.alert('Error', err.message);
    }
  };

  const addSparePart = () => {
    setFormData(prev => ({
      ...prev,
      spareParts: [...prev.spareParts, '']
    }));
  };

  const updateSparePart = (index, value) => {
    setFormData(prev => ({
      ...prev,
      spareParts: prev.spareParts.map((part, i) => i === index ? value : part)
    }));
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
            onChangeText={(text) => updateSparePart(index, text)}
            placeholder={`Repuesto ${index + 1}`}
          />
        ))}
        <TouchableOpacity onPress={addSparePart} style={styles.addButton}>
          <Text style={styles.addButtonText}>+ Agregar Repuesto</Text>
        </TouchableOpacity>
      </View>

      {/* Botón de envío */}
      <TouchableOpacity 
        onPress={handleSubmit} 
        style={[styles.submitButton, loading && styles.submitButtonDisabled]}
        disabled={loading}
      >
        <Text style={styles.submitButtonText}>
          {loading ? 'Asignando...' : 'Asignar Mantenimiento'}
        </Text>
      </TouchableOpacity>

      {error && (
        <Text style={styles.errorText}>{error}</Text>
      )}
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
  submitButtonDisabled: {
    backgroundColor: '#ccc',
  },
  submitButtonText: {
    color: '#fff',
    textAlign: 'center',
    fontSize: 18,
    fontWeight: 'bold',
  },
  errorText: {
    color: '#dc3545',
    textAlign: 'center',
    marginTop: 10,
  },
});

export default AssignmentForm;
```

### 2. Lista de Mantenimientos
```javascript
// components/MaintenanceList.js
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  RefreshControl,
} from 'react-native';
import MaintenanceService from '../services/MaintenanceService';

const MaintenanceList = ({ onSelectMaintenance }) => {
  const [maintenances, setMaintenances] = useState([]);
  const [loading, setLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadMaintenances();
  }, []);

  const loadMaintenances = async () => {
    setLoading(true);
    try {
      const result = await MaintenanceService.getUnassignedMaintenances();
      setMaintenances(result.data || []);
    } catch (err) {
      console.error('Error cargando mantenimientos:', err);
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadMaintenances();
    setRefreshing(false);
  };

  const renderMaintenance = ({ item }) => (
    <TouchableOpacity 
      style={styles.maintenanceItem}
      onPress={() => onSelectMaintenance(item)}
    >
      <Text style={styles.maintenanceTitle}>
        Mantenimiento #{item.id}
      </Text>
      <Text style={styles.maintenanceInfo}>
        Cliente: {item.client_device?.client?.name}
      </Text>
      <Text style={styles.maintenanceInfo}>
        Dispositivo: {item.client_device?.device?.name}
      </Text>
      <Text style={styles.maintenanceInfo}>
        Tipo: {item.type}
      </Text>
      <Text style={styles.statusText}>
        Estado: {item.status}
      </Text>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Mantenimientos Pendientes</Text>
      
      <FlatList
        data={maintenances}
        renderItem={renderMaintenance}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        ListEmptyComponent={
          <Text style={styles.emptyText}>
            No hay mantenimientos pendientes
          </Text>
        }
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    marginBottom: 15,
  },
  maintenanceItem: {
    backgroundColor: '#fff',
    padding: 15,
    marginBottom: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  maintenanceTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  maintenanceInfo: {
    fontSize: 14,
    color: '#666',
    marginTop: 5,
  },
  statusText: {
    fontSize: 14,
    color: '#007bff',
    marginTop: 5,
    fontWeight: '600',
  },
  emptyText: {
    textAlign: 'center',
    fontSize: 16,
    color: '#666',
    marginTop: 50,
  },
});

export default MaintenanceList;
```

### 3. Pantalla Principal
```javascript
// screens/AssignmentScreen.js
import React, { useState } from 'react';
import { View, StyleSheet, Modal } from 'react-native';
import MaintenanceList from '../components/MaintenanceList';
import AssignmentForm from '../components/AssignmentForm';

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
    // Recargar la lista de mantenimientos
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

## Manejo de Errores Específicos

```javascript
// utils/ErrorHandler.js
export const handleAssignmentError = (error) => {
  const errorMessages = {
    'technician_id': 'El técnico seleccionado no está disponible para esta fecha y turno',
    'maintenance_id': 'Este mantenimiento ya fue asignado o está en progreso',
    'date_maintenance': 'Formato de fecha inválido. Use YYYY-MM-DD',
    'shift': 'Turno debe ser AM o PM',
    'spare_parts': 'Debe incluir al menos un repuesto',
  };

  if (error.data) {
    // Error de validación con detalles
    const firstError = Object.keys(error.data)[0];
    return errorMessages[firstError] || error.message;
  }

  return error.message || 'Error desconocido';
};
```

## Dependencias Requeridas

```json
{
  "dependencies": {
    "@react-native-async-storage/async-storage": "^1.19.0",
    "@react-native-picker/picker": "^2.4.0",
    "react-native": "^0.72.0"
  }
}
```

## Instalación

```bash
npm install @react-native-async-storage/async-storage @react-native-picker/picker
```

Para iOS:
```bash
cd ios && pod install
```

Esta implementación proporciona una interfaz completa para asignar mantenimientos desde React Native, con manejo de errores, validaciones y una experiencia de usuario fluida.
