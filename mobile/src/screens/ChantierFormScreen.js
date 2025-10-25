import React, { useState } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  KeyboardAvoidingView,
  Platform,
  Alert,
} from 'react-native';
import { TextInput, Button } from 'react-native-paper';
import { useNavigation, useRoute } from '@react-navigation/native';
import { chantiersService } from '../services/api';

export default function ChantierFormScreen() {
  const navigation = useNavigation();
  const route = useRoute();
  const { mode } = route.params;

  const [nom, setNom] = useState('');
  const [description, setDescription] = useState('');
  const [adresse, setAdresse] = useState('');
  const [dateDebut, setDateDebut] = useState('');
  const [dateFinPrevue, setDateFinPrevue] = useState('');
  const [budgetTotal, setBudgetTotal] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async () => {
    if (!nom || !adresse || !dateDebut) {
      Alert.alert('Erreur', 'Veuillez remplir les champs obligatoires');
      return;
    }

    setLoading(true);
    try {
      const data = {
        nom,
        description,
        adresse,
        date_debut: dateDebut,
        date_fin_prevue: dateFinPrevue || null,
        budget_total: parseFloat(budgetTotal) || 0,
        statut: 'planification',
      };

      const response = await chantiersService.create(data);

      if (response.success) {
        Alert.alert('Succès', 'Chantier créé avec succès');
        navigation.goBack();
      }
    } catch (error) {
      console.error('Error creating chantier:', error);
      Alert.alert('Erreur', 'Impossible de créer le chantier');
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView style={styles.content}>
        <TextInput
          label="Nom du chantier *"
          value={nom}
          onChangeText={setNom}
          style={styles.input}
        />

        <TextInput
          label="Description"
          value={description}
          onChangeText={setDescription}
          multiline
          numberOfLines={3}
          style={styles.input}
        />

        <TextInput
          label="Adresse *"
          value={adresse}
          onChangeText={setAdresse}
          style={styles.input}
        />

        <TextInput
          label="Date de début (YYYY-MM-DD) *"
          value={dateDebut}
          onChangeText={setDateDebut}
          placeholder="2024-01-01"
          style={styles.input}
        />

        <TextInput
          label="Date de fin prévue (YYYY-MM-DD)"
          value={dateFinPrevue}
          onChangeText={setDateFinPrevue}
          placeholder="2024-12-31"
          style={styles.input}
        />

        <TextInput
          label="Budget total (€)"
          value={budgetTotal}
          onChangeText={setBudgetTotal}
          keyboardType="numeric"
          style={styles.input}
        />

        <Button
          mode="contained"
          onPress={handleSubmit}
          loading={loading}
          disabled={loading}
          style={styles.button}
        >
          Créer le chantier
        </Button>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f6fa',
  },
  content: {
    padding: 15,
  },
  input: {
    marginBottom: 15,
  },
  button: {
    marginVertical: 20,
  },
});
