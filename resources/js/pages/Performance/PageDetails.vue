<template>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <div class="flex items-center mb-6">
            <a href="/performance" class="text-indigo-600 hover:text-indigo-900 mr-4">
              &larr; Back to Dashboard
            </a>
            <h1 class="text-2xl font-bold">Page Performance: {{ urlPath }}</h1>
          </div>
          
          <!-- Core Web Vitals Summary -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Performance Metrics</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div v-for="(metric, name) in pageData.metrics" :key="name" 
                   class="bg-white p-4 rounded-lg shadow border">
                <div class="flex justify-between items-center">
                  <h3 class="text-lg font-medium">{{ getMetricName(name) }}</h3>
                  <span :class="getStatusClass(metric.status)" class="px-2 py-1 rounded text-xs font-medium">
                    {{ metric.status }}
                  </span>
                </div>
                <div class="mt-2">
                  <div class="text-3xl font-bold">{{ formatMetricValue(name, metric.value) }}</div>
                  <div class="text-sm text-gray-500">
                    {{ metric.sample_size }} samples
                    <span v-if="metric.change !== 'N/A'" 
                          :class="getChangeClass(metric.change)">
                      {{ metric.change }}
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
                  <option value="fcp">First Contentful Paint</option>
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
                    <tr v-for="(device, index) in pageData.device_breakdown" :key="index">
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
          
          <!-- Browser Breakdown -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Browser Breakdown</h2>
            <div class="bg-white p-4 rounded-lg shadow border">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Browser
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
                    <tr v-for="(browser, index) in pageData.browser_breakdown" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ browser.browser }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ (browser.value / 1000).toFixed(2) }}s
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="getStatusClass(browser.status)" class="px-2 py-1 rounded text-xs font-medium">
                          {{ browser.status }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ browser.sample_size }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          
          <!-- Recommendations -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Recommendations</h2>
            <div class="bg-white p-4 rounded-lg shadow border">
              <ul class="list-disc pl-5 space-y-2">
                <li v-if="hasIssue('lcp')">
                  <strong>Improve Largest Contentful Paint:</strong> Consider optimizing images, reducing server response time, or implementing critical CSS.
                </li>
                <li v-if="hasIssue('cls')">
                  <strong>Reduce Layout Shifts:</strong> Set explicit dimensions for images and embeds, avoid inserting content above existing content.
                </li>
                <li v-if="hasIssue('inp')">
                  <strong>Improve Interaction Responsiveness:</strong> Optimize JavaScript execution, use web workers for heavy tasks, or implement code splitting.
                </li>
                <li v-if="hasIssue('onload_time')">
                  <strong>Reduce Page Load Time:</strong> Minimize render-blocking resources, optimize and compress images, implement lazy loading.
                </li>
                <li v-if="hasMobileIssue()">
                  <strong>Optimize for Mobile:</strong> Mobile performance is significantly worse than desktop. Consider implementing a responsive design or AMP version.
                </li>
              </ul>
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
    pageData: {
      type: Object,
      required: true
    },
    urlPath: {
      type: String,
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
    
    formatMetricValue(metricName, value) {
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
    
    getChangeClass(change) {
      if (change.startsWith('-')) {
        return 'text-green-500 ml-2';
      } else if (change === '0%' || change === 'N/A') {
        return 'text-gray-500 ml-2';
      } else {
        return 'text-red-500 ml-2';
      }
    },
    
    hasIssue(metricName) {
      const metric = this.pageData.metrics[metricName];
      return metric && (metric.status === 'needs-improvement' || metric.status === 'poor');
    },
    
    hasMobileIssue() {
      const mobileData = this.pageData.device_breakdown.find(device => device.device_type === 'mobile');
      const desktopData = this.pageData.device_breakdown.find(device => device.device_type === 'desktop');
      
      if (mobileData && desktopData) {
        const mobileDesktopDiff = (mobileData.value - desktopData.value) / desktopData.value * 100;
        return mobileDesktopDiff > 20; // Mobile is 20% slower than desktop
      }
      
      return false;
    },
    
    fetchTrendData() {
      // In a real implementation, this would fetch trend data from the API
      // and update a chart using a library like Chart.js
      fetch(`/api/performance/trends?metric=${this.selectedMetric}&days=${this.selectedDays}&url_path=${encodeURIComponent(this.urlPath)}`)
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
