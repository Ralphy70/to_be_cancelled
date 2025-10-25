import React, { useState } from 'react';
import { View, StyleSheet, ScrollView, Alert } from 'react-native';
import { TextInput, Button } from 'react-native-paper';
import { useNavigation, useRoute } from '@react-navigation/native';
import { postesService } from '../services/api';

export default function PosteFormScreen() {
  const navigation = useNavigation();
  const route = useRoute();
  const { chantier_id, mode } = route.params;

  const [nom, setNom] = useState('');
  const [description, setDescription] = useState('');
  const [budgetAlloue, setBudgetAlloue] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async () => {
    if (!nom) {
      Alert.alert('Erreur', 'Le nom est obligatoire');
      return;
    }

    setLoading(true);
    try {
      const data = {
        chantier_id,
        nom,
        description,
        budget_alloue: parseFloat(budgetAlloue) || 0,
      };

      const response = await postesService.create(data);

      if (response.success) {
        Alert.alert('Succès', 'Poste créé avec succès');
        navigation.goBack();
      }
    } catch (error) {
      console.error('Error creating poste:', error);
      Alert.alert('Erreur', 'Impossible de créer le poste');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      <View style={styles.content}>
        <TextInput
          label="Nom du poste *"
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
          label="Budget alloué (€)"
          value={budgetAlloue}
          onChangeText={setBudgetAlloue}
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
          Créer le poste
        </Button>
      </View>
    </ScrollView>
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
