<template>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <h1 class="text-2xl font-bold mb-6">Performance Dashboard</h1>
          
          <!-- Core Web Vitals Summary -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Core Web Vitals</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div v-for="(metric, index) in summaryData.core_vitals" :key="index" 
                   class="bg-white p-4 rounded-lg shadow border">
                <div class="flex justify-between items-center">
                  <h3 class="text-lg font-medium">{{ getMetricName(metric.name) }}</h3>
                  <span :class="getStatusClass(metric.status)" class="px-2 py-1 rounded text-xs font-medium">
                    {{ metric.status }}
                  </span>
                </div>
                <div class="mt-2">
                  <div class="text-3xl font-bold">{{ formatMetricValue(metric) }}</div>
                  <div class="text-sm text-gray-500">
                    {{ metric.sample_size }} samples
                    <span v-if="metric.change !== 'N/A'" 
                          :class="getChangeClass(metric.change)">
                      {{ metric.change }}
                    </span>
                  </div>
                </div>
              </div>
              
              <!-- Onload Time -->
              <div class="bg-white p-4 rounded-lg shadow border">
                <div class="flex justify-between items-center">
                  <h3 class="text-lg font-medium">Onload Time</h3>
                  <span :class="getStatusClass(summaryData.onload_time.status)" class="px-2 py-1 rounded text-xs font-medium">
                    {{ summaryData.onload_time.status }}
                  </span>
                </div>
                <div class="mt-2">
                  <div class="text-3xl font-bold">{{ (summaryData.onload_time.value / 1000).toFixed(2) }}s</div>
                  <div class="text-sm text-gray-500">
                    {{ summaryData.onload_time.sample_size }} samples
                    <span v-if="summaryData.onload_time.change !== 'N/A'" 
                          :class="getChangeClass(summaryData.onload_time.change)">
                      {{ summaryData.onload_time.change }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Performance Trends -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Performance Trends</h2>
            <div class="bg-white p-4 rounded-lg shadow border">
              <div class="flex space-x-4 mb-4">
                <select v-model="selectedMetric" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                  <option value="lcp">Largest Contentful Paint</option>
                  <option value="cls">Cumulative Layout Shift</option>
                  <option value="inp">Interaction to Next Paint</option>
                  <option value="onload_time">Onload Time</option>
                </select>
                <select v-model="selectedDays" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                  <option :value="7">Last 7 days</option>
                  <option :value="30">Last 30 days</option>
                  <option :value="90">Last 90 days</option>
                </select>
              </div>
              
              <div class="h-64">
                <!-- Placeholder for chart - in a real implementation, you would use a charting library -->
                <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded">
                  <p class="text-gray-500">Performance trend chart would be displayed here</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Device Breakdown -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Device Breakdown</h2>
            <div class="bg-white p-4 rounded-lg shadow border">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Device Type
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        LCP
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Sample Size
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(device, index) in summaryData.device_breakdown" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ device.device_type }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ (device.value / 1000).toFixed(2) }}s
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="getStatusClass(device.status)" class="px-2 py-1 rounded text-xs font-medium">
                          {{ device.status }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ device.sample_size }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          
          <!-- Top Pages -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Top Pages</h2>
            <div class="bg-white p-4 rounded-lg shadow border">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Page
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        LCP
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        CLS
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Onload Time
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(page, index) in summaryData.page_breakdown" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <a :href="`/performance/page${page.url_path}`" class="text-indigo-600 hover:text-indigo-900">
                          {{ page.url_path }}
                        </a>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ (page.lcp.value / 1000).toFixed(2) }}s</div>
                        <div>
                          <span :class="getStatusClass(page.lcp.status)" class="px-2 py-1 rounded text-xs font-medium">
                            {{ page.lcp.status }}
                          </span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ page.cls.value.toFixed(3) }}</div>
                        <div>
                          <span :class="getStatusClass(page.cls.status)" class="px-2 py-1 rounded text-xs font-medium">
                            {{ page.cls.status }}
                          </span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ (page.onload_time.value / 1000).toFixed(2) }}s</div>
                        <div>
                          <span :class="getStatusClass(page.onload_time.status)" class="px-2 py-1 rounded text-xs font-medium">
                            {{ page.onload_time.status }}
                          </span>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          
          <!-- Performance Regressions -->
          <div v-if="summaryData.regressions && summaryData.regressions.length > 0" class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Performance Regressions</h2>
            <div class="bg-white p-4 rounded-lg shadow border">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Page
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Metric
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Change
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Current
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Previous
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Severity
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(regression, index) in summaryData.regressions" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <a :href="`/performance/page${regression.url_path}`" class="text-indigo-600 hover:text-indigo-900">
                          {{ regression.url_path }}
                        </a>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ getMetricName(regression.metric) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500 font-medium">
                        +{{ regression.change_percent }}%
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ formatRegressionValue(regression.metric, regression.current) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ formatRegressionValue(regression.metric, regression.previous) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="getSeverityClass(regression.severity)" class="px-2 py-1 rounded text-xs font-medium">
                          {{ regression.severity }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          
          <!-- Report Downloads -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Reports</h2>
            <div class="bg-white p-4 rounded-lg shadow border">
              <div class="flex flex-wrap gap-4">
                <a href="/api/performance/reports/download?type=technical&format=json" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  Technical Report (JSON)
                </a>
                <a href="/api/performance/reports/download?type=business&format=json" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                  Business Report (JSON)
                </a>
                <a href="/api/performance/reports/download?type=technical&format=csv" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                  Export as CSV
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  props: {
    summaryData: {
      type: Object,
      required: true
    }
  },
  
  data() {
    return {
      selectedMetric: 'lcp',
      selectedDays: 30
    };
  },
  
  methods: {
    getMetricName(metricCode) {
      const names = {
        'lcp': 'Largest Contentful Paint',
        'cls': 'Cumulative Layout Shift',
        'inp': 'Interaction to Next Paint',
        'fcp': 'First Contentful Paint',
        'onload_time': 'Onload Time'
      };
      
      return names[metricCode] || metricCode;
    },
    
    formatMetricValue(metric) {
      if (metric.name === 'cls') {
        return metric.value.toFixed(3);
      } else {
        return (metric.value / 1000).toFixed(2) + 's';
      }
    },
    
    formatRegressionValue(metricName, value) {
      if (metricName === 'cls') {
        return value.toFixed(3);
      } else {
        return (value / 1000).toFixed(2) + 's';
      }
    },
    
    getStatusClass(status) {
      switch (status) {
        case 'good':
          return 'bg-green-100 text-green-800';
        case 'needs-improvement':
          return 'bg-yellow-100 text-yellow-800';
        case 'poor':
          return 'bg-red-100 text-red-800';
        default:
          return 'bg-gray-100 text-gray-800';
      }
    },
    
    getSeverityClass(severity) {
      switch (severity) {
        case 'high':
          return 'bg-red-100 text-red-800';
        case 'medium':
          return 'bg-yellow-100 text-yellow-800';
        case 'low':
          return 'bg-blue-100 text-blue-800';
        default:
          return 'bg-gray-100 text-gray-800';
      }
    },
    
    getChangeClass(change) {
      if (change.startsWith('-')) {
        return 'text-green-500 ml-2';
      } else if (change === '0%' || change === 'N/A') {
        return 'text-gray-500 ml-2';
      } else {
        return 'text-red-500 ml-2';
      }
    },
    
    fetchTrendData() {
      // In a real implementation, this would fetch trend data from the API
      // and update a chart using a library like Chart.js
      fetch(`/api/performance/trends?metric=${this.selectedMetric}&days=${this.selectedDays}`)
        .then(response => response.json())
        .then(data => {
          console.log('Trend data:', data);
          // Update chart with data
        })
        .catch(error => {
          console.error('Error fetching trend data:', error);
        });
    }
  },
  
  watch: {
    selectedMetric() {
      this.fetchTrendData();
    },
    
    selectedDays() {
      this.fetchTrendData();
    }
  },
  
  mounted() {
    this.fetchTrendData();
  }
};
</script>
