<template>
  <Layout role="teacher">
    <v-container fluid>
      <div class="materials-page">
        <v-row>
          <v-col cols="12">
            <h1 class="text-h4 mb-6">Мои материалы</h1>
          </v-col>
        </v-row>
        
        <v-row>
          <v-col cols="12">
            <v-card>
              <v-card-title>
                Материалы для уроков
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="addMaterial">
                  <v-icon left>mdi-plus</v-icon>
                  Добавить материал
                </v-btn>
              </v-card-title>
              <v-card-text>
                <p>Здесь будут отображаться ваши материалы для уроков.</p>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </div>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import Layout from '../../Layout.vue'

const addMaterial = () => {
  // Логика добавления материала
  console.log('Добавление материала')
}
</script>
