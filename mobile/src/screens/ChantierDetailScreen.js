import React, { useState, useEffect } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
} from 'react-native';
import {
  Card,
  Title,
  Text,
  Chip,
  Button,
  ProgressBar,
  Divider,
} from 'react-native-paper';
import { useNavigation, useRoute } from '@react-navigation/native';
import {
  chantiersService,
  postesService,
  financementsService,
} from '../services/api';
import {
  formatMontant,
  formatDate,
  translateStatus,
  getStatusColor,
  calculatePercentage,
  getProgressColor,
} from '../utils/helpers';

export default function ChantierDetailScreen() {
  const [chantier, setChantier] = useState(null);
  const [postes, setPostes] = useState([]);
  const [financements, setFinancements] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigation = useNavigation();
  const route = useRoute();
  const { id } = route.params;

  useEffect(() => {
    loadData();
  }, [id]);

  const loadData = async () => {
    try {
      const [chantierRes, postesRes, financementsRes] = await Promise.all([
        chantiersService.getById(id),
        postesService.getByChantier(id),
        financementsService.getByChantier(id),
      ]);

      if (chantierRes.success) setChantier(chantierRes.chantier);
      if (postesRes.success) setPostes(postesRes.postes);
      if (financementsRes.success) setFinancements(financementsRes.financements);
    } catch (error) {
      console.error('Error loading chantier:', error);
      Alert.alert('Erreur', 'Impossible de charger les données');
    } finally {
      setLoading(false);
    }
  };

  if (loading || !chantier) {
    return (
      <View style={styles.centerContainer}>
        <Text>Chargement...</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      {/* Informations principales */}
      <Card style={styles.card}>
        <Card.Content>
          <View style={styles.header}>
            <Title>{chantier.nom}</Title>
            <Chip
              style={{ backgroundColor: getStatusColor(chantier.statut) }}
              textStyle={{ color: '#fff' }}
            >
              {translateStatus(chantier.statut)}
            </Chip>
          </View>
          <Text style={styles.description}>{chantier.description}</Text>
          <Divider style={{ marginVertical: 10 }} />
          <Text>📍 {chantier.adresse}</Text>
          <Text>📅 Début: {formatDate(chantier.date_debut)}</Text>
          <Text>📅 Fin prévue: {formatDate(chantier.date_fin_prevue)}</Text>
          <Text style={styles.budget}>
            💰 Budget: {formatMontant(chantier.budget_total)}
          </Text>
        </Card.Content>
      </Card>

      {/* Statistiques */}
      <Card style={styles.card}>
        <Card.Content>
          <Title>Statistiques</Title>
          <View style={styles.statsGrid}>
            <View style={styles.statItem}>
              <Text style={styles.statValue}>
                {formatMontant(chantier.stats?.financement_prevu || 0)}
              </Text>
              <Text style={styles.statLabel}>Financement prévu</Text>
            </View>
            <View style={styles.statItem}>
              <Text style={styles.statValue}>
                {formatMontant(chantier.stats?.financement_verse || 0)}
              </Text>
              <Text style={styles.statLabel}>Fonds versés</Text>
            </View>
          </View>
          <View style={styles.statsGrid}>
            <View style={styles.statItem}>
              <Text style={styles.statValue}>
                {formatMontant(chantier.stats?.budget_alloue || 0)}
              </Text>
              <Text style={styles.statLabel}>Budget alloué</Text>
            </View>
            <View style={styles.statItem}>
              <Text style={styles.statValue}>
                {formatMontant(chantier.stats?.budget_consomme || 0)}
              </Text>
              <Text style={styles.statLabel}>Budget consommé</Text>
            </View>
          </View>
        </Card.Content>
      </Card>

      {/* Postes budgétaires */}
      <Card style={styles.card}>
        <Card.Content>
          <View style={styles.sectionHeader}>
            <Title>Postes budgétaires</Title>
            <Button
              mode="contained"
              compact
              onPress={() =>
                navigation.navigate('PosteForm', {
                  chantier_id: id,
                  mode: 'create',
                })
              }
            >
              Ajouter
            </Button>
          </View>

          {postes.length === 0 ? (
            <Text style={styles.emptyText}>Aucun poste budgétaire</Text>
          ) : (
            postes.map((poste) => {
              const percentage = calculatePercentage(
                poste.budget_consomme,
                poste.budget_alloue
              );
              return (
                <TouchableOpacity
                  key={poste.id}
                  onPress={() =>
                    navigation.navigate('PosteDetail', { id: poste.id })
                  }
                >
                  <View style={styles.posteItem}>
                    <Text style={styles.posteName}>{poste.nom}</Text>
                    <Text style={styles.posteBudget}>
                      {formatMontant(poste.budget_consomme)} /{' '}
                      {formatMontant(poste.budget_alloue)}
                    </Text>
                    <ProgressBar
                      progress={percentage / 100}
                      color={getProgressColor(percentage)}
                      style={styles.progressBar}
                    />
                  </View>
                </TouchableOpacity>
              );
            })
          )}
        </Card.Content>
      </Card>

      {/* Financements */}
      <Card style={styles.card}>
        <Card.Content>
          <Title>Financements</Title>
          {financements.length === 0 ? (
            <Text style={styles.emptyText}>Aucun financement</Text>
          ) : (
            financements.map((financement) => (
              <TouchableOpacity
                key={financement.id}
                onPress={() =>
                  navigation.navigate('FinancementDetail', { id: financement.id })
                }
              >
                <View style={styles.financementItem}>
                  <Text style={styles.financementName}>
                    {financement.financeur_nom}
                  </Text>
                  <Text>
                    {formatMontant(financement.montant_verse)} /{' '}
                    {formatMontant(financement.montant_prevu)}
                  </Text>
                  <Chip
                    style={{
                      backgroundColor: getStatusColor(financement.statut),
                      marginTop: 5,
                    }}
                    textStyle={{ color: '#fff' }}
                  >
                    {translateStatus(financement.statut)}
                  </Chip>
                </View>
              </TouchableOpacity>
            ))
          )}
        </Card.Content>
      </Card>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f6fa',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  card: {
    margin: 10,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  description: {
    color: '#7f8c8d',
    marginBottom: 10,
  },
  budget: {
    fontWeight: 'bold',
    fontSize: 16,
    marginTop: 5,
  },
  statsGrid: {
    flexDirection: 'row',
    marginTop: 10,
  },
  statItem: {
    flex: 1,
    padding: 10,
    backgroundColor: '#ecf0f1',
    borderRadius: 8,
    marginHorizontal: 5,
  },
  statValue: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#2c3e50',
    textAlign: 'center',
  },
  statLabel: {
    fontSize: 11,
    color: '#7f8c8d',
    textAlign: 'center',
    marginTop: 3,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  posteItem: {
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#ecf0f1',
  },
  posteName: {
    fontWeight: 'bold',
    fontSize: 16,
  },
  posteBudget: {
    color: '#7f8c8d',
    marginTop: 3,
  },
  progressBar: {
    marginTop: 5,
    height: 8,
    borderRadius: 4,
  },
  financementItem: {
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#ecf0f1',
  },
  financementName: {
    fontWeight: 'bold',
    fontSize: 16,
  },
  emptyText: {
    color: '#95a5a6',
    textAlign: 'center',
    padding: 10,
  },
});
