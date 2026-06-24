interface CreateTeamLeaderPayload {
  name: string
  mobile: string
  email?: string
  dob?: string
  gender?: string
  kyc_type: string
  pan_number: string
  aadhaar_number?: string
  company_name?: string
  company_type?: string
  gst_number?: string
  address: Record<string, string>
  beneficiary?: {
    type: string
    account_number: string
    holder_name: string
    ifsc_code?: string
    bank_name?: string
    upi_id?: string
  }
}

export const useAdvisorTeamLeader = () => {
  const config = useRuntimeConfig()
  const createUrl = `${config.public.apiBase}/api/dashboard/advisor/team-leaders`

  const createTeamLeader = async (payload: FormData) => {
    return useSanctumFetch(createUrl, {
      method: 'POST',
      body: payload
    })
  }

  return {
    createTeamLeader
  }
}
