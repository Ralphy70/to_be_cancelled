import React, { useState } from 'react';
import { View, StyleSheet, ScrollView, Alert } from 'react-native';
import { TextInput, Button } from 'react-native-paper';
import { useNavigation, useRoute } from '@react-navigation/native';
import { depensesService } from '../services/api';

export default function DepenseFormScreen() {
  const navigation = useNavigation();
  const route = useRoute();
  const { poste_id } = route.params;

  const [description, setDescription] = useState('');
  const [montant, setMontant] = useState('');
  const [dateDepense, setDateDepense] = useState('');
  const [fournisseur, setFournisseur] = useState('');
  const [numeroFacture, setNumeroFacture] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async () => {
    if (!description || !montant || !dateDepense) {
      Alert.alert('Erreur', 'Veuillez remplir les champs obligatoires');
      return;
    }

    setLoading(true);
    try {
      const data = {
        poste_id,
        description,
        montant: parseFloat(montant),
        date_depense: dateDepense,
        fournisseur,
        numero_facture: numeroFacture,
        statut: 'payee',
      };

      const response = await depensesService.create(data);

      if (response.success) {
        Alert.alert('Succès', 'Dépense créée avec succès');
        navigation.goBack();
      }
    } catch (error) {
      console.error('Error creating depense:', error);
      Alert.alert('Erreur', 'Impossible de créer la dépense');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      <View style={styles.content}>
        <TextInput
          label="Description *"
          value={description}
          onChangeText={setDescription}
          style={styles.input}
        />

        <TextInput
          label="Montant (€) *"
          value={montant}
          onChangeText={setMontant}
          keyboardType="numeric"
          style={styles.input}
        />

        <TextInput
          label="Date (YYYY-MM-DD) *"
          value={dateDepense}
          onChangeText={setDateDepense}
          placeholder="2024-01-01"
          style={styles.input}
        />

        <TextInput
          label="Fournisseur"
          value={fournisseur}
          onChangeText={setFournisseur}
          style={styles.input}
        />

        <TextInput
          label="Numéro de facture"
          value={numeroFacture}
          onChangeText={setNumeroFacture}
          style={styles.input}
        />

        <Button
          mode="contained"
          onPress={handleSubmit}
          loading={loading}
          disabled={loading}
          style={styles.button}
        >
          Créer la dépense
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
