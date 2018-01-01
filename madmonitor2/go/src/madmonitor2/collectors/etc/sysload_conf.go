package etc

func Get_config() map[string]string{
	config := map[string]string{
		"collection_interval": "15",    // Seconds, how often to collect metric data
		"collect_every_cpu":   "1",   // True will collect statistics for every CPU, False for the "ALL" CPU
	}
	return config
}